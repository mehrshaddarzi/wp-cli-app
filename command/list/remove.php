<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\Wordpress;

/**
 * Remove _list folder
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app list:remove
 *       remove list folder
 *
 * @when before_wp_load
 */
function wp_cli_app_list_remove( $args, $assoc_args ) {

	//Get Base Path of list folder
	$list_dir = FileSystem::path_join( Wordpress::get_base_path(), $GLOBALS['WP_CLI_APP']['list_format_dir'] );
	FileSystem::remove_dir( $list_dir, true );

	//Success
	CLI::success( "Removed `{$GLOBALS['WP_CLI_APP']['list_format_dir']}` folder." );

}