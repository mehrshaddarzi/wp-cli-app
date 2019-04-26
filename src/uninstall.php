<?php

use WP_CLI_APP\Package\Utility\temp;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Package\Package;
use WP_CLI_APP\Package\Utility\install;
use WP_CLI_APP\Package\Utility\validation;

/**
 * Delete WordPress Site and Package.
 *
 * ## OPTIONS
 *
 * [--force]
 * : force remove.
 *
 * [--backup]
 * : get Backup before removing WordPress.
 *
 * ## DOCUMENT
 *
 *      https://docs.wp-cli-app.com/uninstall
 *
 * ## EXAMPLES
 *
 *      # Delete WordPress Package.
 *      $ wp app install
 *      Success: Completed install WordPress.
 *
 * @when before_wp_load
 */
\WP_CLI::add_command( 'uninstall', function ( $args, $assoc_args ) {

	//Remove Package LocalTemp
	temp::remove_temp_file( PHP::getcwd() );

	//Show Success
	CLI::success( CLI::_e( 'package', 'created' ) );
} );