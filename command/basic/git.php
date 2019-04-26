<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Git;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Using Git control Version command manager For Workspace.
 *
 * <method>
 * : Method name Of Git e.g `wp app Git init`
 *
 * [<arg>]
 * : First Argument For Composer Method e.g `wp app git add ...`
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
 *     $ wp app git init
 *
 */
function wp_cli_app_basic_git( $args, $assoc_args ) {

	//Check Git is installed in System
	Git::is_exist_git();

	//Check Active Workspace
	WorkSpace::is_active_workspace();
	$workspace = WorkSpace::get_workspace();

	//Disable Clone Command
	if ( isset( $args[0] ) and ! empty( $args[0] ) and strtolower( $args[0] ) == "clone" ) {
		CLI::log("For Clone Project, Use This command:", true);
		Git::help_clone_command();
		return;
	}

	//Create Custom Git Cli
	$arg = array();
	for ( $x = 0; $x <= 3; $x ++ ) {
		if ( isset( $args[ $x ] ) and ! empty( $args[ $x ] ) ) {
			$arg[] = $args[ $x ];
		}
	}

	//Run command
	Git::run_git($workspace['path'], $arg);
}