<?php

/**
 * enable or disabled Debug Developer in Wordpress
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
 *     $ wp app set:debug true
 *
 * @version 1.0.0
 *
 */
function wp_cli_app_set_debug( $args, $assoc_args ) {
	$validation = array("false", "true");

	//Check Validation Value
	if( ! in_array($args[0], $validation) ) {
		WP_CLI::error( "Please Set (true or false) Value For Debug" );
	}

	//Set Debug Wordpress
	WP_CLI::runcommand( "config set WP_DEBUG ".$args[0]." --raw --type=constant" );
};