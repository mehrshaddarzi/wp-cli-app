<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Run Composer Package command for this Workspace
 *
 * <method>
 * : Method name Of composer e.g `wp app composer init`
 *
 * [<arg>]
 * : First Argument For Composer Method e.g `wp app composer require ...`
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
 *     $ wp app composer install
 *
 */
function wp_cli_app_basic_composer( $args, $assoc_args ) {

	//Check Composer is installed in System
	if ( CLI::command_exists( "composer" ) === false ) {
		CLI::error( "Composer Package Manager is not active in your system, read more : https://getcomposer.org/doc/00-intro.md" );
		return;
	}

	//Check Active Workspace
	WorkSpace::is_active_workspace();
	$workspace = WorkSpace::get_workspace();

	//Create Custom Composer Cli
	$arg = array();
	for ( $x = 0; $x <= 3; $x ++ ) {
		if ( isset( $args[ $x ] ) and ! empty( $args[ $x ] ) ) {
			$arg[] = $args[ $x ];
		}
	}

	//Run command
	CLI::run_composer( $workspace['path'], $arg );
}