<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Show Current Workspace
 *
 * [--dir]
 * : View Folder of Project
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app workspace
 *      Show information current Workspace
 *
 *      $ wp app workspace --dir
 *      Show explorer Workspace Directory
 *
 */
function wp_cli_app_basic_workspace( $args, $assoc_args ) {

	//Check Active Workspace
	WorkSpace::is_active_workspace();

	//Get Current WorkSpace
	$workspace = WorkSpace::get_workspace();

	//Show Folder Workspace
	if ( isset( $assoc_args['dir'] ) ) {

		CLI::Browser( $workspace['path'] );
	} else {

		//Show workSpace Detail
		WorkSpace::workspace_info_table();
	}
}