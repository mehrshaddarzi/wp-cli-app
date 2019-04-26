<?php

use WP_CLI_APP\Utility\CLI;

/**
 * Increase PHP Memory for Wordpress
 *
 * [<value>]
 * : value is int number > 0
 *
 * [--e]
 * : empty define Memory Constant
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:memory 64
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_set_memory( $args, $assoc_args ) {

	/**
	 * Check is Empty Space
	 */
	if ( isset( $assoc_args['e'] ) ) {
		$config = new WPConfigTransformer( getcwd() . '/wp-config.php' );
		$config->remove( 'constant', 'WP_MEMORY_LIMIT' );
		CLI::success( "Removed WP_MEMORY_LIMIT constant." );
	} else {

		//Check Validation Value
		$new_number = preg_replace( '/[^0-9]/', '', $args[0] );
		if ( empty( $new_number ) || ! is_numeric( $new_number ) || $new_number < 1 ) {
			WP_CLI::error( "Please Set integer Number Value For PHP Memory." );
		}

		//Set PHP Memory Wordpress
		WP_CLI::runcommand( "config set WP_MEMORY_LIMIT " . $new_number . "M --type=constant" );
	}
}