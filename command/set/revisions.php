<?php

/**
 * Limit the number of posts revisions that WordPress stores in the database.
 *
 * [<value>]
 * : value is true or False or int number > 0
 *
 * [--e]
 * : empty define Post Revisions
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:revisions 20
 *
 * @version 1.0.0
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_set_revisions( $args, $assoc_args ) {

	/**
	 * Check is Empty Space
	 */
	if ( isset( $assoc_args['e'] ) ) {
		$config = new WPConfigTransformer( getcwd() . '/wp-config.php' );
		$config->remove( 'constant', 'WP_POST_REVISIONS' );
		Cli::success( "Removed WP_POST_REVISIONS constant." );
	} else {

		$validation = array( "false", "true" );

		//Check Validation Value
		if ( ! in_array( $args[0], $validation ) || empty( $args[0] ) ) {
			if ( ! is_numeric( $args[0] ) ) {
				WP_CLI::error( "Please Set (true or false or integer number) Value For Post Revisions" );
			}
		}

		//Check Value
		$value = $args[0];
		if ( is_numeric( $args[0] ) and $args[0] < 1 ) {
			$value = false;
		}

		//Set Post Revisions Wordpress
		WP_CLI::runcommand( "config set WP_POST_REVISIONS " . $value . " --raw --type=constant" );
	}
}