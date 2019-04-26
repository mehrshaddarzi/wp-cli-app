<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FTP;

/**
 * Set FTP Remote For Manage Wordpress Site in Another Server
 *
 * [--ftp_host=<host>]
 * : ftp Host
 *
 * [--ftp_login=<login>]
 * : Ftp Login
 *
 * [--ftp_password=<password>]
 * : ftp Password
 *
 * [--ftp_port=<21>]
 * : ftp Port
 *
 * [--website_url=<url>]
 * : Your wordpress Site Url
 *
 * [--ssl_connect]
 * : Is SSl connect (sftp)
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:remote
 *
 * @when before_wp_load
 */
function wp_cli_app_set_remote( $args, $assoc_args ) {

	/** Force Only run With --Prompt */
	if ( ! isset ( $assoc_args['prompt'] ) and count( $assoc_args ) == 0 ) {
		WP_CLI::runcommand( "app set:remote --prompt" );
		return;
	}

	//Check Empty Arg
	$require_key = array( "ftp_host", "ftp_login", "ftp_password", "website_url" );
	foreach ( $require_key as $r ) {
		if ( empty( $assoc_args[ $r ] ) ) {
			CLI::error( "Please Enter `$r` Field." );
			exit;
		}
	}

	//Sanitize arg
	$port         = CLI::get_flag_value( $assoc_args, 'port', '21' );
	$is_ssl       = CLI::get_flag_value( $assoc_args, 'ssl_connect', 0 );
	$passive_mode = CLI::get_flag_value( $assoc_args, 'passive_mode', 1 );
	$url          = CLI::get_flag_value( $assoc_args, 'website_url', '' );
	$url          = filter_var( trim( $url ), FILTER_SANITIZE_URL );
	if ( ! empty( $url ) and function_exists( 'parse_url' ) ) {
		if ( $ret = parse_url( $url ) ) {
			if ( ! isset( $ret["scheme"] ) ) {
				$url = "http://{$url}";
			}
		}
	}

	//Set Number Step
	$step = 3;

	//Check Ftp Connect
	$ftp           = new FTP();
	$ftp->host     = trim( $assoc_args['ftp_host'] );
	$ftp->login    = trim( $assoc_args['ftp_login'] );
	$ftp->password = trim( $assoc_args['ftp_password'] );
	$ftp->is_ssl   = $is_ssl;
	$ftp->port     = $port;
	$ftp->passive  = $passive_mode;
	$ftp->domain   = rtrim( $url, "/" );
	$conn          = $ftp->connect();
	if ( $conn === false ) {
		CLI::error( "Could Not connect To Your FTP Server.Please check entry." );
	} else {
		CLI::log( CLI::color( "Step 1/" . $step . ":", "b" ) . " The connection to the ftp server was successful." );
	}

	//get wp-config File
	CLI::log( "We are finding the WordPress `wp-config.php` file in your server. please wait ..." );
	$current_path   = $conn->getDirectory();
	$wp_config_path = $conn->ftp_file_search( $current_path, true, "wp-config.php" );
	if ( $wp_config_path != false ) {
		CLI::log( CLI::color( "Step 2/" . $step . ":", "b" ) . " WordPress folder was found successfully." );
		$ftp->wp_directory = str_replace( "wp-config.php", "", $wp_config_path );
	} else {
		CLI::error( "WordPress folder not found on your server." );
	}

	//Check Domain Name
	$check_domain = $ftp->check_ftp_domain( $ftp->domain, $ftp->wp_directory );
	if ( $check_domain === false ) {
		CLI::error( "We were unable to connect to your WordPress Domain.Please Check your Domain and tray again." );
	} else {
		$ftp->wp_content = $check_domain['wp_content'];
		$ftp->mu_plugins = $check_domain['mu_plugins'];
		$ftp->wp_uploads = $check_domain['wp_uploads'];
		$ftp->wp_themes  = $check_domain['wp_themes'];
		$ftp->wp_plugins = $check_domain['wp_plugins'];
		CLI::log( CLI::color( "Step 3/" . $step . ":", "b" ) . " Get Your Wordpress information successfully." );
	}

	//Save
	$save = $ftp->save_config( array(
		'host'         => $ftp->host,
		'login'        => $ftp->login,
		'password'     => $ftp->password,
		'is_ssl'       => $ftp->is_ssl,
		'passive'      => $ftp->passive,
		'port'         => $ftp->port,
		'domain'       => $ftp->domain,
		'wp_directory' => $ftp->wp_directory,
		'wp_content'   => $ftp->wp_content,
		'mu_plugins'   => $ftp->mu_plugins,
		'wp_uploads'   => $ftp->wp_uploads,
		'wp_themes'    => $ftp->wp_themes,
		'wp_plugins'   => $ftp->wp_plugins
	) );
	if ( $save === false ) {
		CLI::error( "The file information of the remote file is not saved. Please try again." );
	} else {
		CLI::success( "Set Remote Config file successfully." );
	}

}