<?php

use WP_CLI_APP\Utility\Backup;


/**
 * Get backup From uploads folder
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
 *     $ wp app backup:uploads
 *     get backup File From uploads folder
 *
 *     $ wp app backup:uploads my_name
 *     get backup File From uploads folder with name my_name.zip
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_backup_uploads( $args, $assoc_args ) {

	//Run command
	$backup = new Backup();
	$backup->run_backup( "uploads", $args, $assoc_args );
}