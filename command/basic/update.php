<?php

/**
 * Update Wordpress Core and All Plugin
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 * ## EXAMPLES
 *
 *     $ wp app update
 *
 *
 * @subcommand update
 *
 */
function wp_cli_app_basic_update( $args, $assoc_args ) {

	//Update Wordpress Core
	$options = array(
		'return'     => true,
		'parse'      => 'json',
		'launch'     => true,
		'exit_error' => false,
	);
	delete_option( 'core_updater' );
	WP_CLI::runcommand( "core update", $options );
	if ( is_multisite() ) {
		WP_CLI::runcommand( "core update-db --network", $options  );
	} else {
		WP_CLI::runcommand( "core update-db", $options  );
	}
	WP_CLI::log( "Success : WordPress core updated." );

	//Update Plugin
	WP_CLI::confirm( "do you want to Update All Plugins too ?", $assoc_args );
	WP_CLI::runcommand( "plugin update --all" );
}