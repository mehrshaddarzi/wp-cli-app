<?php

use WP_CLI_APP\Package\Arguments\Locale;
use WP_CLI_APP\Utility\Wordpress;

/**
 * Change of Wordpress Language
 *
 * <lang>
 * : Your Language key for example en_US
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:lang fa_IR
 *
 * @version 1.0.0
 */
function wp_cli_app_set_lang( $args, $assoc_args ) {

	//install Language
	WP_CLI::runcommand( "language core install " . $args[0] );

	//Activate Language
	WP_CLI::runcommand( "site switch-language " . $args[0] );

	//Check Country Name
	$country = Locale::get_wordpress_locale( trim( $args[0] ) );
	if ( $country['status'] === true ) {
		$country = $country['data'] . " Country ";
	}

	//Success
	WP_CLI::success( $country . "Language [" . $args[0] . "] installed successfully." );
}