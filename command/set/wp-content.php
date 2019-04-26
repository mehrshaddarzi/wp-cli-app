<?php

use WP_CLI_APP\Utility\FileSystem;

/**
 * Change of wp-content folder name
 *
 * <name>
 * : the new name of the wp-content folder
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:wp-content public
 *
 * @version 1.0.0
 */
function wp_cli_app_set_wp_content( $args, $assoc_args ) {

	$new_folder = FileSystem::sanitize_folder_name($args[0]);

	//check Folder exist
	$config_transformer = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	if ( ! $config_transformer->exists( 'constant', 'WP_CONTENT_DIR' ) ) {
		$now_folder = 'wp-content';
	} else {
		$now_folder = str_replace(ABSPATH, "",WP_CONTENT_DIR);
	}
	if( $new_folder == $now_folder ) {
		WP_CLI::error( "`$new_folder` folder is now exist." );
		return;
	}

	//check if wp-config.php
	if ( ! $config_transformer->exists( 'constant', 'WP_CONTENT_DIR' ) ) {
		$config_transformer->add( 'constant', 'WP_HOME', rtrim( get_option("siteurl"), "/" ), array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_SITEURL', rtrim( get_option("siteurl"), "/" ), array( 'raw' => false, 'normalize' => true ) );
	}
	$config_transformer->update( 'constant', 'WP_CONTENT_FOLDER', $new_folder, array( 'raw' => false, 'normalize' => true ) );
	$config_transformer->update( 'constant', 'WP_CONTENT_DIR', 'ABSPATH . WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ) );
	$config_transformer->update( 'constant', 'WP_CONTENT_URL', 'WP_SITEURL.\'/\'.WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ) );

	//rename Folder
	rename( getcwd() . '/'.$now_folder, getcwd() . '/' . $new_folder );

	//check reset folders
	FileSystem::Reset_Basic_Folder_Wordpress();

	//Success
	WP_CLI::success( "wp-content folder renamed to `$new_folder` completely." );
}