<?php

use WP_CLI_APP\Utility\Wordpress;

/**
 * Change of user password
 *
 * <password>
 * : New Password
 *
 * [--user=<value>]
 * : user ID or user_email or user_login
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:password 123456 --user=1
 *
 * @version 1.0.0
 */
function wp_cli_app_set_password( $args, $assoc_args ) {
	global $wpdb;

	//Check User id exist
	if ( isset( $assoc_args['user'] ) and $assoc_args['user'] != "" ) {
		$user = Wordpress::get_user_info( trim( $assoc_args['user'] ) );
		if ( $user != false ) {
			$user_id = $user->ID;
		} else {
			WP_CLI::error( "User is not found." );
		}
		wp_set_password( $args[0], $user_id );
		WP_CLI::success( "The `{$user->user_login}` User password is changed to `{$args[0]}`." );

	} else {
		//Set Password for First User ID
		$get_admin_user = $wpdb->get_row( "SELECT * FROM {$wpdb->users} ORDER BY `ID` ASC LIMIT 1", ARRAY_A );
		$user_id        = $get_admin_user['ID'];
		wp_set_password( $args[0], $user_id );

		WP_CLI::success( "The `{$get_admin_user['user_login']}` User password is changed to `{$args[0]}`." );
	}
}