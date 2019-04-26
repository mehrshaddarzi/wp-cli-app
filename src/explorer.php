<?php

/**
 * Explorer Current working directory.
 *
 * ## DOCUMENT
 *
 *      https://docs.wp-cli-app.com/explorer/
 *
 * ## EXAMPLES
 *
 *      # Explorer Current working directory
 *      $ wp explorer
 *
 * @when before_wp_load
 */

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\PHP;

\WP_CLI::add_command( 'explorer', function ( $args, $assoc_args ) {
	CLI::Browser( PHP::getcwd() );
} );