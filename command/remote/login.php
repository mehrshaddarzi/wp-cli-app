<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Remote;

/**
 * Automatic login By ID Or user_login Or user_email
 *
 * [<ID>]
 * : user ID or user_email or user_login
 *
 * [--go=<URL>]
 * : Redirect to page after Login complete e.g : admin Or home
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app remote:login admin
 *      Automatic login as `admin` user_login in wordpress
 *
 * @version 1.0.0
 * @when before_wp_load
 * @throws Exception
 */
function wp_cli_app_remote_login( $args, $assoc_args ) {

	//init Remote
	$remote = new Remote();

	//Check Validate command
	if ( ! isset( $args[0] ) ) {
		CLI::log( "Your command is wrong. Please request the following table :", true );
		$list = array(
			array(
				'key'         => 'ID',
				'command'     => 'wp app remote:login 1',
				'description' => 'login as user with `ID` = `1`',
			),
			array(
				'key'         => 'user_login',
				'command'     => 'wp app remote:login admin',
				'description' => 'login as user with `user_login` = `admin`',
			),
			array(
				'key'         => 'user_email',
				'command'     => 'wp app remote:login info@site.com',
				'description' => 'login as user with `user_email` = `info@site.com`',
			)
		);
		CLI::create_table( $list, true );
		exit;
	}

	//Set Where
	$arg = array( "user_search" => $args[0] );

	//Check redirect
	if ( isset( $assoc_args['go'] ) ) {
		$arg['go_after_login'] = trim( $assoc_args['go'] );
	}

	//Run
	$remote->run( "login", $arg );
}