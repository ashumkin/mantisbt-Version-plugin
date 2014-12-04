<?php

# Copyright (c) 2012 Alexey Shumkin
# Licensed under the MIT license

$t_address = $_SERVER['REMOTE_ADDR'];
$t_valid = false;

# Always allow the same machine to check-in
if ( '127.0.0.1' == $t_address || '127.0.1.1' == $t_address
     || 'localhost' == $t_address || '::1' == $t_address ) {
	$t_valid = true;
}

# Check for allowed remote IP/URL addresses
if ( !$t_valid ) {
	$t_version_update_urls = unserialize( plugin_config_get( 'remote_version_update_urls' ) );
	preg_match( '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $t_address, $t_address_matches );

	foreach ( $t_version_update_urls as $t_url ) {
		if ( $t_valid ) break;

		$t_url = trim( $t_url );

		if ( preg_match( '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $t_url, $t_remote_matches ) ) { # IP
			if ( $t_url == $t_address ) {
				$t_valid = true;
				break;
			}

			$t_match = true;
			for( $i = 1; $i <= 4; $i++ ) {
				if ( $t_remote_matches[$i] == '0' || $t_address_matches[$i] == $t_remote_matches[$i] ) {
				} else {
					$t_match = false;
					break;
				}
			}

			$t_valid = $t_match;

		} else {
			$t_ip = gethostbyname( $t_url );
			if ( $t_ip == $t_address ) {
				$t_valid = true;
				break;
			}
		}
	}
}

if ( gpc_get_string( 'api_key', '' ) == plugin_config_get( 'api_key' ) && trim(plugin_config_get( 'api_key' )) != '') {
	$t_valid = true;
}

# Not validated by this point gets the boot!
if ( !$t_valid ) {
	die( plugin_lang_get( 'invalid_remote_version_update_url' ) );
}

$f_date = gpc_get_string( 'date', '' );
$f_date = strtotime( $f_date );
$f_debug = gpc_get_bool( 'debug', false );
$f_version_name = gpc_get_string( 'version' );
$f_encoding = gpc_get_string( 'encoding', 'UTF-8' );
$t_project_name = gpc_get_string( 'project' );
if ( $f_encoding ) {
	if (! $t_project_name = iconv( $f_encoding, 'UTF-8', $t_project_name ) ) {
		die( plugin_lang_get( 'invalid_project_encoding' ) );
	}
}
$t_project_id = project_get_id_by_name( $t_project_name );

# Project not found
if ( is_null( $t_project_id ) || $t_project_id == 0 ) {
	die( plugin_lang_get( 'invalid_project' ) );
}

$t_version_name = plugin_version_array( $f_version_name );
$t_version_token_count = plugin_config_get( 'version_token_count' );
while ( count( $t_version_name ) > $t_version_token_count ) {
	array_pop( $t_version_name );
}
$t_version_name = implode( '.', $t_version_name );
$t_version_id = version_get_id( $t_version_name, $t_project_id );
if ( false === $t_version_id ) {
	die( plugin_lang_get( 'invalid_version' ) . ': '. $t_version_name );
}
$t_version = version_get( $t_version_id );
$t_predata = event_signal( 'EVENT_VERSION_INCREMENT', array( $t_version, $f_date, $f_debug ) );

