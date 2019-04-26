<?php

use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\SRDB;

/**
 * Change of Uploads folder name
 *
 * <name>
 * : the new name of the Uploads folder
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:uploads file
 *
 * @version 1.0.0
 */
function wp_cli_app_set_uploads( $args, $assoc_args ) {

	$new_folder = FileSystem::sanitize_folder_name( $args[0] );

	//Check uploads Folder exist
	$upload_dir = str_replace( "\\", "/", wp_upload_dir()['basedir'] );
	$upload_dir = array_slice( explode( "/", $upload_dir ), - 2, 2, true );
	$upload_dir = implode( "/", $upload_dir ); //->wp-content/uploads
	if ( FileSystem::folder_exist( path_join( ABSPATH, explode( "/", $upload_dir )[0] . "/" . $new_folder ) ) ) {
		WP_CLI::error( "`$new_folder` folder is now exist." );

		return;
	}


	//Check if wp-content exist
	$config_transformer = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	if ( ! $config_transformer->exists( 'constant', 'WP_CONTENT_DIR' ) and $new_folder !="uploads" ) {
		$config_transformer->update( 'constant', 'WP_HOME', rtrim( get_option( "siteurl" ), "/" ), array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->update( 'constant', 'WP_SITEURL', rtrim( get_option( "siteurl" ), "/" ), array( 'raw' => false, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_FOLDER', 'wp-content', array( 'raw'  => false, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_DIR', 'ABSPATH . WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ));
		$config_transformer->update( 'constant', 'WP_CONTENT_URL', 'WP_SITEURL.\'/\'.WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ));
	}

	//remove if new name is uploads
	if($new_folder =="uploads") {
		if ( $config_transformer->exists( 'constant', 'UPLOADS' ) ) {
			$config_transformer->remove( 'constant', 'UPLOADS' );
		}
	} else {
		if ( $config_transformer->exists( 'constant', 'WP_CONTENT_FOLDER' ) ) {
			$new_folder = "WP_CONTENT_FOLDER . '/".$new_folder."'";
		} else {
			$new_folder = "wp-content/'.$new_folder.'";
		}
		$config_transformer->add( 'constant', 'UPLOADS', $new_folder , array( 'raw' => false, 'normalize' => true ) );
	}


	//rename Folder
	rename( path_join( ABSPATH, $upload_dir ), path_join( ABSPATH, explode( "/", $upload_dir )[0] . "/" . $new_folder ) );


	//change all url in database
	//we can not to use search-replace command [because this command is echo table]
	$srdb       = new SRDB();
	$table_list = $srdb::get_tables();
	foreach ( $table_list as $tbl ) {
		$args = array(
			'case_insensitive' => 'off',
			'replace_guids'    => 'off',
			'dry_run'          => 'off',
			'search_for'       => path_join( rtrim( get_option( "siteurl" ), "/" ), $upload_dir ) . "/",
			'replace_with'     => path_join( rtrim( get_option( "siteurl" ), "/" ), explode( "/", $upload_dir )[0] . "/" . $new_folder ) . "/",
			'completed_pages'  => 0,
		);
		$srdb->srdb( $tbl, $args );
	}

	//check reset folders
	FileSystem::Reset_Basic_Folder_Wordpress();

	//Success
	WP_CLI::success( "uploads folder renamed to `$new_folder` and updated all link in Database completely." );
}