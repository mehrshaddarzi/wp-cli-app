<?php

/**
 * Reset MySql Database Wordpress
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app reset
 *
 */
function wp_cli_app_basic_reset( $args, $assoc_args ) {
	global $wpdb;
	WP_CLI::confirm( "Are you sure you want to reset the `" . DB_NAME . "` database ?", $assoc_args );

	/**
	 * Get site Detail before drop database
	 */
	$siteurl        = get_option( 'siteurl' );
	$sitename       = get_option( 'blogname' );
	$get_admin_user = $wpdb->get_row( "SELECT * FROM {$wpdb->users} ORDER BY `ID` ASC LIMIT 1", ARRAY_A );
	$admin_user     = $get_admin_user['user_login'];
	$admin_pass     = "admin";
	$admin_email    = get_option( 'admin_email' );

	//Please Wait
	WP_CLI::log( "\n" . WP_CLI::colorize( "%yPlease Wait ...%n" ) );

	/** Drop Database */
	$options = array( 'return' => true, 'parse' => 'json', 'launch' => true, 'exit_error' => true );
	WP_CLI::runcommand( "db reset --yes", $options );

	/** Setup Again Database */
	WP_CLI::runcommand( "core install --url=" . $siteurl . " --title=" . $sitename . " --admin_user=" . $admin_user . " --admin_password=" . $admin_pass . " --admin_email=" . $admin_email . "", $options );

	//success
	WP_CLI::log( WP_CLI::colorize( "%GSuccess:%n" ) . " Wordpress Database is reset successfully." );
	WP_CLI::log( "\n----------\nAdmin Url : " . rtrim( $siteurl, "/" ) . "/wp-login.php\nUserName: " . $admin_user . "\nPassword: " . $admin_pass . "\n----------" );
}