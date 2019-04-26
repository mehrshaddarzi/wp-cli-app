<?php

/**
 * Refreshes the salts defined
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app make:salt
 *
 *
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_make_salt( $args, $assoc_args ) {

	/**
	 * List Of constant
	 */
	$constant_list = array(
		'AUTH_KEY',
		'SECURE_AUTH_KEY',
		'LOGGED_IN_KEY',
		'NONCE_KEY',
		'AUTH_SALT',
		'SECURE_AUTH_SALT',
		'LOGGED_IN_SALT',
		'NONCE_SALT'
	);

	//Generate New Value
	$secret_keys = array();
	foreach ( $constant_list as $key ) {
		$secret_keys[ $key ] = \WP_CLI_APP\Utility\FileSystem::random_key();
	}

	//Set New Data
	$config = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	foreach ( $secret_keys as $constant => $key ) {
		$config->update( 'constant', $constant, (string) $key );
	}

	//Success
	\WP_CLI_APP\Utility\CLI::success("Salt keys have been completely Updated.");

}