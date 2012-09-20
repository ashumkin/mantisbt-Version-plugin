<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the MIT license

form_security_validate( 'plugin_Version_manage_config' );
auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'manage_threshold' ) );

$f_update_threshold = gpc_get_int( 'update_threshold' );
$f_manage_threshold = gpc_get_int( 'manage_threshold' );

$f_change_target_version_to_next= gpc_get_bool( 'enable_change_target_version_to_next', OFF );
$f_description_template = gpc_get_string( 'description_template' );

function check_urls( $t_urls_in ) {
	$t_urls_in = explode( "\n", $t_urls_in );
	$t_urls_out = array();

	foreach( $t_urls_in as $t_url ) {
		$t_url = trim( $t_url );
		if ( is_blank( $t_url ) || in_array( $t_url, $t_urls_out ) ) {
			continue;
		}

		$t_urls_out[] = $t_url;
	}

	return $t_urls_out;
}

$f_version_update_urls = gpc_get_string( 'remote_version_update_urls' );

$t_version_update_urls = check_urls( $f_version_update_urls );

$f_api_key = gpc_get_string( 'api_key' );
$f_increment_date_by_days = gpc_get_string( 'increment_date_by_days' );

function maybe_set_option( $name, $value ) {
	if ( $value != plugin_config_get( $name ) ) {
		plugin_config_set( $name, $value );
	}
}

maybe_set_option( 'update_threshold', $f_update_threshold );
maybe_set_option( 'manage_threshold', $f_manage_threshold );

maybe_set_option( 'enable_change_target_version_to_next', $f_change_target_version_to_next );
maybe_set_option( 'description_template', $f_description_template );

maybe_set_option( 'remote_version_update_urls', serialize( $t_version_update_urls ) );

maybe_set_option( 'api_key', $f_api_key );
maybe_set_option( 'increment_date_by_days', $f_increment_date_by_days );

form_security_purge( 'plugin_Version_manage_config' );

print_successful_redirect( plugin_page( 'config', true ) );

