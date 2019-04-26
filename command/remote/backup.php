<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Remote;

/**
 * Create backup From Wordpress database Or Files
 *
 * [<area>]
 * : area of backup
 *
 * [--d]
 * : Start download File after Complete Backup
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app remote:backup db
 *      Get Backup From Wordpress Database Remote
 *
 * @version 1.0.0
 * @when before_wp_load
 * @throws Exception
 */
function wp_cli_app_remote_backup( $args, $assoc_args ) {

	//init Remote
	$remote = new Remote();

	//Run Command
	$where = array( "db", "files", "plugins", "themes", "uploads", "wp-content", "remove", "list" );
	if ( ! in_array( $args[0], $where ) ) {
		CLI::log( "Your command is wrong. Please request the following table :", true );
		$list = array(
			array(
				'key'         => 'db',
				'command'     => 'wp app remote:backup db',
				'description' => 'create Wordpress Database Backup',
			),
			array(
				'key'         => 'files',
				'command'     => 'wp app remote:backup files',
				'description' => 'create zip Backup From all wordpress files',
			),
			array(
				'key'         => 'plugins',
				'command'     => 'wp app remote:backup plugins',
				'description' => 'create zip Backup From wordpress plugins folder',
			),
			array(
				'key'         => 'themes',
				'command'     => 'wp app remote:backup themes',
				'description' => 'create zip Backup From wordpress themes folder',
			),
			array(
				'key'         => 'uploads',
				'command'     => 'wp app remote:backup uploads',
				'description' => 'create zip Backup From wordpress uploads folder',
			),
			array(
				'key'         => 'wp-content',
				'command'     => 'wp app remote:backup wp-content',
				'description' => 'create zip Backup From wordpress wp-content folder',
			),
			array(
				'key'         => 'remove',
				'command'     => 'wp app remote:backup remove',
				'description' => 'remove all wordpress backups in remote server',
			),
			array(
				'key'         => 'list',
				'command'     => 'wp app remote:backup list',
				'description' => 'get list of backup files in remote server',
			)
		);
		CLI::create_table( $list, true );
		exit;
	}

	//Set Where
	$arg = array( "where" => $args[0] );

	//Check Start Download After Complete
	if ( isset( $assoc_args['d'] ) ) {
		$arg['download_after'] = true;
	}

	//Run
	$remote->run( "backup", $arg );
}