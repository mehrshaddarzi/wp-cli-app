<?php

use WP_CLI_APP\Utility\Backup;
use WP_CLI_APP\Utility\CLI;

/**
 * Backup From Wordpress Files and Database
 *
 * [--c]
 * : CleanUp backup folder
 *
 * [--remove=<ID>]
 * : Remove Backup File By File ID or Filename
 *
 * [--list]
 * : list of all backup Files
 *
 * [--s=<Search>]
 * : Search in backup files
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app backup
 *      get full backup Wordpress
 *
 *      $ wp app backup --list
 *      Show List Of Backup file
 *
 *      $ wp app backup --list --s=sql
 *      Show List Of all sql file
 *
 *      $ wp app backup --r=5
 *      Remove backup file with ID 5
 *
 *      $ wp app backup --remove=archive.zip
 *      Remove backup file with name `archive.zip`
 *
 *
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_basic_backup( $args, $assoc_args ) {

	//Create New Object backup Class
	$backup = new Backup();
	$start  = time();

	//CleanUp Backup file
	if ( isset( $assoc_args['c'] ) ) {

		CLI::confirm("Are you sure you want to drop the all backup Files ?");
		$remove      = $backup->remove();
		$number_file = '';
		if ( $remove > 0 ) {
			$number_file = "(" . number_format( $remove ) . " Files)";
		}
		CLI::success( "Backup files is removed Completely." . $number_file );

	} elseif ( isset( $assoc_args['list'] ) ) {

		//Check Search Type
		$search = '';
		if ( isset( $assoc_args['s'] ) ) {
			$search = $assoc_args['s'];
		}

		//Get List Of Backup files
		$list_backup = $backup->get_list_backup( $search );
		if ( count( $list_backup ) > 0 ) {
			CLI::create_table( $list_backup, true, false );
		} else {
			CLI::error( "no found file with search keyword `" . $search . "`." );
		}

	} elseif ( isset( $assoc_args['remove'] ) ) {

		//Check exist file
		$exist = $backup->search_backup_file( trim( $assoc_args['remove'] ) );
		if ( $exist === false ) {
			CLI::error( "File with the " . ( is_numeric( trim( $assoc_args['remove'] ) ) ? 'ID' : 'name' ) . " `" . trim( $assoc_args['remove'] ) . "` not found in backup Folder." );
		} else {
			$remove = $backup->remove_backup_file( $exist );
			if ( $remove ) {
				CLI::success( "Backup File with name `" . $exist . "` removed completely." );
			} else {
				CLI::error( "Error in Remove File `" . $exist . "`." );
			}
		}

	} else {

		//Add Sql Backup
		$database = $backup->database();
		CLI::log( CLI::color( "Step 1/2:", "b" ) . " Database Backup File With Name `$database` is Created." );

		//Add Full backup
		$full = $backup->full_backup();
		CLI::log( CLI::color( "Step 2/2:", "b" ) . " Wordpress Backup File With Name `" . $full . "` is Created." );

		//Success
		sleep( 1 );
		CLI::br();
		$process_time = CLI::process_time( $start );
		$time         = "(Process time: " . $process_time . ")";
		CLI::success( "Backup Process is Completed." . $time );

		//Show Table information
		$db_info   = $backup->show_inf_file_tbl( $database );
		$full_info = $backup->show_inf_file_tbl( $full );
		CLI::create_table( array( $db_info[0], $full_info[0] ), true );

	}

}
