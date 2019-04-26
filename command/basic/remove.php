<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\DB;
use WP_CLI_APP\Utility\FileSystem;

/**
 * Remove All Wordpress Files And Database Completely
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app remove
 *
 */
function wp_cli_app_basic_remove( $args, $assoc_args ) {
	CLI::confirm( "Are you sure you want to drop the database and all Wordpress Files ?" );

	/** Drop Database */
	DB::drop_wp_db( array(
		'dbhost' => DB_HOST,
		'dbuser' => DB_USER,
		'dbpass' => DB_PASSWORD,
		'dbname' => DB_NAME
	) );
	WP_CLI::log( "Step 1/2 : Removed `" . DB_NAME . "` Database." );


	/** Drop All file */
	FileSystem::remove_dir( ABSPATH );
	WP_CLI::log( "Step 2/2 : Removed Wordpress Files." );


	//success
	WP_CLI::success( "Successfully Removed Wordpress." );
}