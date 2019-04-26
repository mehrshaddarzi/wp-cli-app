<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\Git;
use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Utility\WorkSpace;
use WP_CLI_APP\Package\Arguments\Dir;

/**
 * Clone a Project in Wordpress Themes dir
 *
 * <url>
 * : Git clone Repository Url e.g `wp app clone:theme https://github.com..../repo.git`
 *
 * [<arg>]
 * : First Argument For Composer Method
 *
 * [<arg>]
 * : Second Argument For Composer Method
 *
 * [--w]
 * : Set Plugin as WorkSpace
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app clone:theme https://github.com..../repo.git new_folder_name
 *
 */
function wp_cli_app_clone_theme( $args, $assoc_args ) {

	//Check Git is installed in System
	Git::is_exist_git();

	//Get Plugin Base Dir
	$themes_path = Dir::get_themes_dir();

	//Run command
	$arg = array( "clone", $args[0] );
	for ( $x = 1; $x <= 2; $x ++ ) {
		if ( isset( $args[ $x ] ) and ! empty( $args[ $x ] ) ) {
			$arg[] = $args[ $x ];
		}
	}
	Git::run_git( $themes_path, $arg );

	//Set this WorkSpace
	if ( isset( $assoc_args['w'] ) ) {
		sleep(3);
		$plugins_dir_list = FileSystem::sort_dir_by_date($themes_path, "DESC");
		$last_dir = $plugins_dir_list[0];
		WP_CLI::runcommand( "app set:workspace --theme=".$last_dir );
	}
}