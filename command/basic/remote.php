<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FTP;
use WP_CLI_APP\Utility\WorkSpace;

/**
 * Show/Add Remote FTP server information
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app remote
 *
 * @param $args
 * @param $assoc_args
 * @when before_wp_load
 * @throws Exception
 */
function wp_cli_app_basic_remote( $args, $assoc_args ) {

	//Check exist FTP Remote
	$ftp        = new FTP();
	$get_config = $ftp->check_ftp_opt();
	if ( $get_config != false ) {
		CLI::br();
		CLI::log( "Current FTP remote Server :" );
		$list = array();
		foreach ( $get_config as $o_key => $o_val ) {

			//Password
			if ( $o_key == "password" ) {
				$pass = '';
				for ( $i = 0; $i <= strlen( $o_val ); $i ++ ) {
					$pass .= '*';
				}
				$o_val = $pass;
			}

			//Show Ssl or passive Mode
			if ( $o_key == "is_ssl" || $o_key == "passive" ) {
				if ( $o_val == 1 ) {
					$o_val = 'Yes';
				} else {
					$o_val = 'No';
				}
			}

			$list[] = array(
				'option'   => $o_key,
				'value' => $o_val,
			);
		}
		CLI::create_table( $list, true );
		exit;
	}

	//Set New FTP Remote
	WP_CLI::runcommand( "app set:remote --prompt" );
	return;
}