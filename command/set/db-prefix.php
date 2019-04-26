<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\DB;
use WP_CLI_APP\Utility\FileSystem;

/**
 * Rename Wordpress Database Prefix
 *
 * <prefix>
 * : New Prefix Table e.g : key_
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:dbprefix key_
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_set_db_prefix( $args, $assoc_args ) {
	global $wpdb;

	//TODO add flag for create backup before run --b

	//Check New Prefix
	$prefix = FileSystem::sanitize_folder_name( str_replace( array( "-", "_" ), array( "", "" ), $args[0] ) ) . '_';

	//Confirm
	CLI::confirm( "Are you sure you want to rename " . parse_url( site_url(), PHP_URL_HOST ) . "'s database prefix from `" . $wpdb->prefix . "` to `" . $prefix . "`?" );

	//Change Process
	if ( DB::change_prefix_db( $prefix ) === true ) {
		CLI::success( "Successfully renamed database prefix." );
	}
}