<?php

use WP_CLI_APP\Utility\Optimize;


/**
 * optimize and repair Wordpress Database
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app optimize
 *
 */
function wp_cli_app_basic_optimize( $args, $assoc_args ) {

	//Please Wait
	WP_CLI::log( "\n" . WP_CLI::colorize( "%yPlease Wait ...%n" ) );

	//Run Optimize and repair
	Optimize::optimize_db();

	//success
	WP_CLI::success( "The WordPress database has been successfully optimized and repaired." );
}