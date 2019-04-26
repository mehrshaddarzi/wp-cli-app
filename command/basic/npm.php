<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Run Npm Command for this Workspace
 *
 * <method>
 * : Method name Of npm e.g `wp app npm init`
 *
 * [<arg>]
 * : First Argument For Composer Method e.g `wp app npm install ...`
 *
 * [<arg>]
 * : Second Argument For Composer Method
 *
 * [<arg>]
 * : Third Argument For Composer Method
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app npm install gulp
 *
 */
function wp_cli_app_basic_npm( $args, $assoc_args ) {

	//Check Npm is installed in System
	if ( CLI::command_exists( "npm" ) === false ) {
		CLI::error( "Node js Package Manager (npm) is not active in your system, read more : https://nodejs.org/en/" );
		return;
	}

	//Check Active Workspace
	WorkSpace::is_active_workspace();
	$workspace = WorkSpace::get_workspace();
	$pure_path = ltrim( str_replace( Wordpress::get_base_path(), "", $workspace['path'] ), "/" );

	//Create Custom Npm Cli
	$arg = array( "npm" );
	for ( $x = 0; $x <= 3; $x ++ ) {
		if ( isset( $args[ $x ] ) and ! empty( $args[ $x ] ) ) {
			$arg[] = $args[ $x ];
		}
	}

	//Conditions For init Npm
	if ( $args[0] == "init" ) {
		$arg = array( "npm", "init -y" );
	}

	//Go To WorkSpace
	chdir( $pure_path );

	//Run Command
	CLI::exec( implode(" ", $arg) );
}