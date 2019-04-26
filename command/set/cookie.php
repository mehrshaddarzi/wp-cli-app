<?php

use WP_CLI_APP\Utility\CLI;

/**
 * Change Of Default Cookie constant
 *
 * [<value>]
 * : value is a prefix for Wordpress Cookie
 *
 * [--e]
 * : empty all define Cookie Constant
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:cookie my_key_
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_set_cookie( $args, $assoc_args ) {

	/**
	 * Default constant
	 * @see https://developer.wordpress.org/reference/functions/wp_cookie_constants/
	 */
	$list = array(
		"COOKIEHASH",
		"USER_COOKIE",
		"PASS_COOKIE",
		"AUTH_COOKIE",
		"SECURE_AUTH_COOKIE",
		"LOGGED_IN_COOKIE",
		"TEST_COOKIE"
	);

	//Get Config File
	$config = new WPConfigTransformer( getcwd() . '/wp-config.php' );

	/**
	 * Check is Empty Space
	 */
	if ( isset( $assoc_args['e'] ) ) {

		/**
		 * Remove all Default constant
		 */
		foreach ( $list as $const ) {
			$config->remove( 'constant', $const );
		}

		//Success
		CLI::success("Cookie constants are removed.");

	} else {

		//Check Validation Value
		if ( is_numeric( $args[0] ) || empty( $args[0] ) ) {
			WP_CLI::error( "Please Set a String Value For Cookie Prefix. e.g : my_key_" );
		}

		/**
		 * Remove all Default constant
		 */
		foreach ( $list as $const ) {
			$config->remove( 'constant', $const );
		}

		/**
		 * Add New Constant
		 */
		if ( trim( $args[0] ) != "wordpress" ) {
			foreach ( $list as $const ) {
				switch ( $const ) {
					case "COOKIEHASH":
						$site_url = get_option( "siteurl" );
						$config->update( 'constant', $const, wp_hash_password( $site_url ), array( 'raw' => false, 'normalize' => true ) );
						break;
					case "USER_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "user_' . COOKIEHASH", array( 'raw' => true, 'normalize' => true ) );
						break;
					case "PASS_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "pass_' . COOKIEHASH", array( 'raw' => true, 'normalize' => true ) );
						break;
					case "AUTH_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "_' . COOKIEHASH", array( 'raw' => true, 'normalize' => true ) );
						break;
					case "SECURE_AUTH_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "_sec_' . COOKIEHASH", array( 'raw' => true, 'normalize' => true ) );
						break;
					case "LOGGED_IN_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "_login_' . COOKIEHASH", array( 'raw' => true, 'normalize' => true ) );
						break;
					case "TEST_COOKIE":
						$config->update( 'constant', $const, "'" . $args[0] . "_cookie_test'", array( 'raw' => true, 'normalize' => true ) );
						break;
				}
			}
		}

		//Success
		CLI::success( "completed defines WordPress Cookie constants." );
	}

}