<?php

/**
 * Flushing Rewrite Rules Or Cache in Wordpress
 *
 * [<area>]
 * : area of Flushing
 *
 * ----- List ----
 * url -> Flushing Rewrite Rules
 * cache -> Remove all Backup files
 * --------------
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app flush
 *
 */
function wp_cli_app_basic_flush( $args, $assoc_args ) {

	//List Of Area
	$items = array(
		array(
			'name'        => 'rewrite',
			'example'     => 'wp app flush rewrite',
			'description' => 'Flushing Rewrite Rules',
		),
		array(
			'name'        => 'cache',
			'example'     => 'wp app flush cache',
			'description' => 'Flush Wordpress cache',
		),
	);

	//If not Ser Area
	if ( ! isset( $args[0] ) ) {

		WP_CLI::log( "\n" . "Please Enter Of Area For Flushing, lists of the area :" );
		WP_CLI\Utils\format_items( 'table', $items, array( 'name', 'example', 'description' ) );

	} else {

		$validation = array( "rewrite", "cache" );

		//Check Validation Value
		if ( ! in_array( $args[0], $validation ) ) {
			WP_CLI::log( "\n" . WP_CLI::colorize( "%rerror:%n " ) . ": `" . $args[0] . "` is not validation area, lists of the area :" );
			WP_CLI\Utils\format_items( 'table', $items, array( 'name', 'example', 'description' ) );

			return;
		}

		switch ( $args[0] ) {
			case "rewrite":
				//Rewrite Flush
				$options = array( 'return' => true, 'parse' => 'json', 'launch' => true, 'exit_error' => true );
				WP_CLI::runcommand( "rewrite flush", $options );
				WP_CLI::log( WP_CLI::colorize( "%GSuccess:%n" ) . " Rewrite rules flushed." . "\n" );
				break;

			case "cache":
				WP_CLI::runcommand( "cache flush" );
				break;
		}

	}

}