<?php

use WP_CLI_APP\Utility\CLI;

/**
 * Change WordPress Timezone.
 *
 * <timezone>
 * : WordPress Timezone.
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:timezone Asia/Tehran
 *     $ wp app set:timezone UTC-3.5
 *
 */
function wp_cli_app_set_timezone( $args, $assoc_args ) {

	//Search Wordpress TimeZone
	if ( WP_CLI_APP\Package\Arguments\Timezone::search_timezone( $args[0] ) === false ) {
		CLI::error( CLI::_e( 'package', 'wrong_timezone' ) );
	}

	//Change Wordpress TimeZone
	WP_CLI_APP\Package\Arguments\Timezone::update_timezone( $args[0] );

	//Success
	CLI::success( CLI::_e( 'package', 'update_timezone' ) );
}