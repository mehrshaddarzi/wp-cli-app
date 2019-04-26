<?php

use WP_CLI_APP\Utility\Backup;

/**
 * Get backup From plugins folder
 *
 * [<FileName>]
 * : Backup file name
 *
 * [--dir=<Dir>]
 * : Get Backup From Custom folder in plugins dir
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app backup:plugins
 *     get backup File From plugins folder
 *
 *     $ wp app backup:plugins my_name
 *     get backup File From plugins folder with name my_name.zip
 *
 *     $ wp app backup:plugins --dir=akismet
 *     get backup File From akismet plugins folder
 *
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_backup_plugins( $args, $assoc_args ) {

	//Run command
	$backup = new Backup();
	$backup->run_backup( "plugins", $args, $assoc_args );
}