<?php

use WP_CLI_APP\Utility\SRDB;

/**
 * Change of Wordpress Site Url
 *
 * <url>
 * : New Site Url With Protocol , for example https://example.com
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:url http://example.com
 *
 * @version 1.0.0
 */
function wp_cli_app_set_url( $args, $assoc_args ) {

	/** Generate Standard Home Url */
	if ( filter_var( $args[0], FILTER_VALIDATE_URL ) === false ) {
		WP_CLI::error( "Please enter Your New Url with Protocol, for example https://example.com" );

		return;
	}
	$new_url = rtrim( $args[0], "/" );

	//Check confirm User
	WP_CLI::confirm( "Are you sure you want to change wordpress url to `" . $new_url . "` ?", $assoc_args );

	//get now url
	$url = rtrim( get_option( 'siteurl' ), "/" );

	//change url in wp-config.php if exist
	$config_transformer = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	if ( $config_transformer->exists( 'constant', 'WP_HOME' ) ) {
		$config_transformer->update( 'constant', 'WP_HOME', $new_url, array( 'raw' => false, 'normalize' => true ) );
	}
	if ( $config_transformer->exists( 'constant', 'WP_SITEURL' ) ) {
		$config_transformer->update( 'constant', 'WP_SITEURL', $new_url, array( 'raw' => false, 'normalize' => true ) );
	}
	WP_CLI::log( "Step 1/3 : Wordpress Site Url Changed." );


	//search replace in db
	//we can not to use search-replace command [because this command is echo table]
	$srdb       = new SRDB();
	$table_list = $srdb::get_tables();
	foreach ( $table_list as $tbl ) {
		$args = array(
			'case_insensitive' => 'off',
			'replace_guids'    => 'off',
			'dry_run'          => 'off',
			'search_for'       => $url,
			'replace_with'     => $new_url,
			'completed_pages'  => 0,
		);
		$srdb->srdb( $tbl, $args );
	}
	WP_CLI::log( "Step 2/3 : Change New Url in all wordpress database tables." );


	//flush
	$options = array(
		'return'     => true,
		'parse'      => 'json',
		'launch'     => true,
		'exit_error' => true,
	);
	WP_CLI::runcommand( "rewrite flush", $options );
	WP_CLI::log( "Step 3/3 : Flush Rewrite Url." );

	//Report Bug From Github
	update_option( 'siteurl', $new_url );
	update_option( 'home', $new_url );

	//Success
	WP_CLI::success( "Wordpress site url is changed successfully." );
}