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

	protected function release_version($p_version) {
		$p_version->released = VERSION_RELEASED;
		$p_version->date_order = time();
		version_update($p_version);
	}

	public function release_inc_version($p_event, $p_version) {
		$t_version = $p_version->version;
		echo "Incrementing version: ".$t_version;
		$t_version = $this->get_next_by_name( $t_version );
		echo " -> ".$t_version."\n";
		if (!version_is_unique( $t_version, $p_version->project_id )) {
			echo $t_version.": ".error_string(ERROR_VERSION_DUPLICATE);
		} else {
			# release version only if next does not exist
			$this->release_version($p_version);

			$p_version->version = $t_version;
			$p_version->date_order = time() + 24 * 60 * 60 * plugin_config_get('increment_date_by_days');
			$t_description = plugin_config_get('description_template');
			version_add( $p_version->project_id, $p_version->version, VERSION_FUTURE,
				$t_description, $p_version->date_order );
		}
		return $p_version;
	}

	public function version_updated($event, $version_id) {
			form_security_purge( 'manage_proj_ver_update' );
			$t_redirect_url = plugin_page('manage', true);
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
		# increment version last token
		$t_version[count( $t_version ) - 1]++;
		return implode(".", $t_version);
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
		$page = plugin_page("manage");
		$label = plugin_lang_get("version_title");
		return "<a href=\"{$page}\">{$label}</a>";
	}


}
