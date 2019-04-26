<?php

/**
 * enable or disabled WP_Cache
 *
 * <value>
 * : value is true or False
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:cache true
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_set_cache( $args, $assoc_args ) {
	$validation = array( "false", "true" );

	//Check Validation Value
	if ( ! in_array( $args[0], $validation ) ) {
		WP_CLI::error( "Please Set (true or false) Value For WP_CACHE" );
	}

	//Set WP_CACHE Wordpress
	if ( $args[0] == "true" ) {
		WP_CLI::runcommand( "config set WP_CACHE " . $args[0] . " --raw --type=constant" );
	} else {
		$config = new WPConfigTransformer( getcwd() . '/wp-config.php' );
		$config->remove( 'constant', 'WP_CACHE' );
		$config->remove( 'constant', 'WP_CACHE_KEY_SALT' );
		\WP_CLI_APP\Utility\CLI::success( "Deleted the constant 'WP_CACHE'" );
	}

}