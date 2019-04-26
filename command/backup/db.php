<?php

use WP_CLI_APP\Utility\Backup;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;

/**
 * Get backup From database sql File
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
 *     $ wp app backup:db
 *     get backup File From database
 *
 *     $ wp app backup:db my_db
 *     get backup File From database with name my_db.sql
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_backup_db( $args, $assoc_args ) {

	//Create New Backup
	$backup   = new Backup();
	$start    = time();

	//Check file Name
	$file_name = '';
	if ( isset( $args[0] ) ) {
		$file_name = $args[0];
	}

	//Create Backup
	$database = $backup->database( $file_name );
	sleep( 1 );
	if ( file_exists( FileSystem::path_join( $backup->backup_dir_path, $database ) ) ) {
		$process_time = CLI::process_time( $start );
		$time         = "(Process time: " . $process_time . ")";
		CLI::br();
		CLI::success( "Completed Database Backup." . $time );
		$file_info = $backup->show_inf_file_tbl( $database );
		CLI::create_table( $file_info, true );
	} else {
		CLI::error( "Error Taking backup file, Please try again." );
	}

}