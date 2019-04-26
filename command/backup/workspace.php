<?php

use WP_CLI_APP\Utility\Backup;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Get backup From wp-content folder
 *
 * [<FileName>]
 * : Backup file name
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app backup:wp-content
 *     get backup File From wp-content folder
 *
 *     $ wp app backup:wp-content my_name
 *     get backup File From wp-content folder with name my_name.zip
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_backup_workspace( $args, $assoc_args ) {

	//Check active workspace
	WorkSpace::is_active_workspace();

	//Run command
	$backup = new Backup();
	$backup->run_backup( "workspace", $args, $assoc_args );
}