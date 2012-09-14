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
			'enable_change_target_version_to_next' => OFF
		);
	}

	public function hooks() {
		return array(
		    'EVENT_MANAGE_VERSION_UPDATE_FORM' => 'next',
		    'EVENT_MANAGE_VERSION_UPDATE' => 'version_updated',
		    'EVENT_MENU_MANAGE' => 'menu_manage',
		);
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

	public function next($event, $version_id) {
		echo '<tr><td>next version = '.$version_id[0].'</td></tr>';
	}

	public function menu_manage($event, $user_id) {
		$page = plugin_page("manage");
		$label = plugin_lang_get("version_title");
		return "<a href=\"{$page}\">{$label}</a>";
	}


}
