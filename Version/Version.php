<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the GNU license

class VersionPlugin extends MantisPlugin {
	public function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );

		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.2.0'
		);

		$this->author = 'Alexey Shumkin';
		$this->contact = 'Alex.Crezoff@gmail.com';
		$this->url = 'http://github.com/ashumkin';
		$this->page = 'config';
	}

	public function events() {
		return array(
			'EVENT_VERSION_INCREMENT' => EVENT_TYPE_CHAIN
		);
	}

	public function config() {
		return array(
			'api_key' => '',
			'remote_version_update_urls' => serialize( array( 'localhost' ) ),
			'update_threshold'	=> UPDATER,
			'manage_threshold'	=> ADMINISTRATOR,
			'enable_change_target_version_to_next' => OFF,
			'description_template' => '$[future ]release ${version}',
			'increment_date_by_days' => 1
		);
	}

	public function hooks() {
		return array(
		    'EVENT_MANAGE_VERSION_UPDATE_FORM' => 'next',
		    'EVENT_MANAGE_VERSION_UPDATE' => 'version_updated',
		    'EVENT_MENU_MANAGE' => 'menu_manage',
			'EVENT_VERSION_INCREMENT' => 'release_inc_version'
		);
	}

	protected function process_description($p_description, $p_version, $p_for_release) {
		$t_replacement = $p_for_release ? '' : '\1';
		// replace "$[some string]" with empty string or "some string" (depending on flag $p_for_release)
		$t_description = preg_replace( '/\$\[([^\[\]]*)\]/', $t_replacement, $p_description );
		$t_description = preg_replace( '/\$\{version\}/', $p_version->version, $t_description );
		return $t_description;
	}

	protected function release_version($p_version, $p_description_template) {
		// do not modify version if it is already released
		if ( $p_version->released == VERSION_RELEASED ) {
			echo 'Version '.$p_version->version." already released\n";
			return;
		}
		$p_version->released = VERSION_RELEASED;
		$p_version->date_order = time();
		// check whether version description matches our template or not
		$t_description = $this->process_description( $p_description_template, $p_version, false );
		// if matches
		if ( $t_description == $p_version->description ) {
			// replace it by template
			$p_version->description = $this->process_description( $p_description_template, $p_version, true );
		}
		if ( !$this->debug ) {
			version_update( $p_version );
		}
	}

	protected function get_unresolved_bugs($p_version) {
		$t_page_number = 1;
		$t_per_page = -1;
		$t_page_count = 1;
		$t_bug_count = 0;
		echo "Getting unresolved bugs...\n";
		$t_available_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_desired_statuses = array();
		foreach( $t_available_statuses as $t_available_status ) {
			if ( $t_resolved > $t_available_status ) {
				$t_desired_statuses[] = $t_available_status;
			}
		}
		$t_filter = array(
			'_view_type' => 'advanced',
			'project_id' => array( $p_version->project_id ),
			'target_version' => array( $p_version->version ),
			'show_status' => $t_desired_statuses
		);
		$t_bugs = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter );
		return $t_bugs;
	}

	protected function move_unresolved_bugs_to_the_next_version($p_version, $p_next) {
		$t_bugs = $this->get_unresolved_bugs($p_version);
		if ( count($t_bugs) > 0 ) {
			foreach($t_bugs as $t_bug) {
				echo 'Updating bug '.$t_bug->id.' target version to '.$p_next."\n";
				if ( !$this->debug ) {
					bug_set_field( $t_bug->id, 'target_version', $p_next );
				}
				helper_call_custom_function( 'issue_update_notify', array( $t_bug->id ) );
			}
		}
	}

	public function release_inc_version($p_event, $p_version) {
		$t_version = $p_version->version;
		$t_version_next = $this->get_next_by_name( $t_version );
		$this->debug = gpc_get_bool( 'debug' );
		echo 'Incrementing version: '.$t_version.' -> '.$t_version_next."\n";
		if ( !version_is_unique( $t_version_next, $p_version->project_id ) ) {
			echo $t_version_next.': '.error_string( ERROR_VERSION_DUPLICATE );
		} else {
			if ( ON == plugin_config_get( 'enable_change_target_version_to_next' ) ) {
				$this->move_unresolved_bugs_to_the_next_version($p_version, $t_version_next);
			}
			// release version only if next does not exist
			$t_description = plugin_config_get( 'description_template' );
			$this->release_version( $p_version, $t_description );

			$p_version->version = $t_version_next;
			$p_version->date_order = time() + 24 * 60 * 60 * plugin_config_get( 'increment_date_by_days' );
			$t_description = $this->process_description( $t_description, $p_version, false );
			echo 'Adding version '.$t_version_next;
			if ( !$this->debug ) {
				version_add( $p_version->project_id, $p_version->version, VERSION_FUTURE,
					$t_description, $p_version->date_order );
			}
		}
		return $p_version;
	}

	public function version_updated($event, $version_id) {
			form_security_purge( 'manage_proj_ver_update' );
			$t_redirect_url = plugin_page( 'manage', true );
			html_page_top( null, $t_redirect_url );
			?>
<br />
<div align="center">
<?php
		echo lang_get( 'operation_successful' ) . '<br />';

		print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
		html_page_bottom();
		exit;
	}

	protected function get_next_by_name($version) {
		$t_version = plugin_version_array( $version );
		// increment version last token
		$t_version[count( $t_version ) - 1]++;
		return implode( '.', $t_version );
	}

	protected function get_next_by_id($version_id) {
		$t_version = version_get( $version_id );
		$t_version->version = $this->get_next_by_name( $t_version->version );
		return $t_version;
	}

	public function next($event, $version_id) {
		$t_version = $this->get_next_by_id( $version_id );
		echo '<tr '.helper_alternate_class().'><td class="category">next version</td><td>'.$t_version->version.'</td></tr>';
	}

	public function menu_manage($event, $user_id) {
		$page = plugin_page( 'manage' );
		$label = plugin_lang_get( 'version_title' );
		return "<a href=\"{$page}\">{$label}</a>";
	}


}
