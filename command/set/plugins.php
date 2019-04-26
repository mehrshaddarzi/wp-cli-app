<?php

use WP_CLI_APP\Utility\FileSystem;

/**
 * Change of plugins folder name
 *
 * <name>
 * : the new name of the plugins folder
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:plugins addone
 *
 * @version 1.0.0
 */
function wp_cli_app_set_plugins( $args, $assoc_args ) {

	$new_folder = FileSystem::sanitize_folder_name( $args[0] );

	//Check Plugins Folder exist
	$plugin_folder = array_slice( explode( "/", PLUGINDIR ), -2, 2, true );
	$plugin_folder = implode( "/", $plugin_folder ); //->wp-content/plugins
	if ( FileSystem::folder_exist( path_join( ABSPATH, explode("/",$plugin_folder)[0] . "/" . $new_folder ) ) ) {
		WP_CLI::error( "`$new_folder` folder is now exist." );

		return;
	}

	//Check if wp-content exist
	$config_transformer = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	if ( ! $config_transformer->exists( 'constant', 'WP_CONTENT_DIR' ) and $new_folder !="plugins" ) {
		$config_transformer->update( 'constant', 'WP_HOME', rtrim( get_option( "siteurl" ), "/" ), array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->update( 'constant', 'WP_SITEURL', rtrim( get_option( "siteurl" ), "/" ), array( 'raw' => false, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_FOLDER', 'wp-content', array( 'raw'  => false, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_DIR', 'ABSPATH . WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_URL', 'WP_SITEURL.\'/\'.WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ));
	}

	//remove if new name is plugins
	if($new_folder =="plugins") {
		if ( $config_transformer->exists( 'constant', 'WP_PLUGIN_DIR' ) ) {
			$config_transformer->remove( 'constant', 'WP_PLUGIN_DIR' );
		}
		if ( $config_transformer->exists( 'constant', 'PLUGINDIR' ) ) {
			$config_transformer->remove( 'constant', 'PLUGINDIR' );
		}
		if ( $config_transformer->exists( 'constant', 'WP_PLUGIN_URL' ) ) {
			$config_transformer->remove( 'constant', 'WP_PLUGIN_URL' );
		}
	} else {
		//Create if new name !=plugins
		$config_transformer->add( 'constant', 'WP_PLUGIN_DIR', "WP_CONTENT_DIR . '/" . $new_folder . "'", array( 'raw' => true, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'PLUGINDIR', "WP_CONTENT_DIR . '/" . $new_folder . "'", array( 'raw' => true, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_PLUGIN_URL', "WP_CONTENT_URL.'/" . $new_folder . "'", array( 'raw' => true, 'normalize' => true ) );
	}

	//rename Folder
	rename( path_join( ABSPATH, $plugin_folder ), path_join( ABSPATH, explode("/",$plugin_folder)[0]."/".$new_folder ) );

	//check reset folders
	FileSystem::Reset_Basic_Folder_Wordpress();

	//Success
	WP_CLI::success( "plugins folder renamed to `$new_folder` completely." );
}