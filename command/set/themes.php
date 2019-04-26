<?php

use WP_CLI_APP\Utility\FileSystem;

/**
 * Change of Wordpress Themes Dir
 *
 * <name>
 * : the new name of the themes folder
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app set:themes template
 *
 * @version 1.0.0
 */
function wp_cli_app_set_themes( $args, $assoc_args ) {

	//Get Base dir
	$get_theme_root = basename( get_theme_root() );

	//check if dir exist
	if ( FileSystem::folder_exist( path_join( WP_CONTENT_DIR, $args[0] ) ) ) {
		WP_CLI::error( '`' . $args[0] . '` folder exists now.' );
	}

	/*
	 * Change theme folder
	 */
	rename( get_theme_root(), path_join( WP_CONTENT_DIR, $args[0] ) );
	if ( file_exists( path_join( WPMU_PLUGIN_DIR, "theme-dir.php" ) ) ) {
		@unlink( path_join( WPMU_PLUGIN_DIR, "theme-dir.php" ) );
	}
	if ( $args[0] != "themes" ) {
		$mustache = FileSystem::load_mustache();
		FileSystem::file_put_content( path_join( WPMU_PLUGIN_DIR, "theme-dir.php" ), $mustache->render( 'mu-plugins/theme-dir', array( 'dir' => $args[0] ) ) );
	}

	//check reset folders
	FileSystem::Reset_Basic_Folder_Wordpress();

	//Success
	WP_CLI::success( "Wordpress themes folder renamed to `" . $args[0] . "` successfully." );
}