<?php

use WP_CLI_APP\Utility\Backup;

/**
 * Get backup From themes folder
 *
 * [<FileName>]
 * : Backup file name
 *
 * [--dir=<Dir>]
 * : Get Backup From Custom folder in Themes dir
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app backup:themes
 *     get backup File From themes folder
 *
 *     $ wp app backup:themes my_name
 *     get backup File From themes folder with name my_name.zip
 *
 *     $ wp app backup:themes --dir=twentyfifteen
 *     get backup File From twentyfifteen theme folder
 *
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_backup_themes( $args, $assoc_args ) {

	//Run command
	$backup = new Backup();
	$backup->run_backup( "themes", $args, $assoc_args );
}