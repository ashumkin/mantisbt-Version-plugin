<?php

# Copyright (c) 2011 Alexey Shumkin
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
	}

	public function hooks() {
		return array(
		    'EVENT_MANAGE_VERSION_UPDATE_FORM' => 'next',
		    'EVENT_MENU_MANAGE' => 'menu_manage',
		);
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
