<?php

use WP_CLI_APP\Utility\Wordpress;

/**
 * Automatic login By ID Or user_login Or user_email
 *
 * [<ID>]
 * : user ID or user_email or user_login
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:login 1
 *
 * @version 1.0.0
 */
function wp_cli_app_set_login( $args, $assoc_args ) {

	//Check arg exist
	if ( isset( $args[0] ) and trim( $args[0] ) != "" ) {

		//Get User id
		$user = Wordpress::get_user_info( trim( $args[0] ) );
		if ( $user != false ) {
			$user_id = $user->ID;
		} else {
			WP_CLI::error( "User is not found." );
			exit;
		}

		//Please Wait
		WP_CLI::log( "\n" . WP_CLI::colorize( "%yPlease Wait ...%n" ) );

		//Login User
		Wordpress::set_current_user( $user_id, get_option( "home" ) );

	} else {

		WP_CLI::error( "Please enter User ID or email or UserLogin, for example `wp app set:login admin`" );
	}
}