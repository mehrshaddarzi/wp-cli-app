<?php

use WP_CLI_APP\Utility\WorkSpace;

/**
 * Set Workspace for Developing Plugins OR Theme Wordpress
 *
 *
 * [--plugin=<Plugin>]
 * : Set a Plugin for workspace `wp app set:workspace --plugin=akismet`
 *
 * [--theme=<Theme>]
 * : Set a Theme For workspace `wp app set:workspace --theme=twentysixteen`
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app set:workspace --plugin=akismet
 *
 * @version 1.0.0
 */
function wp_cli_app_set_workspace( $args, $assoc_args ) {

	//IF Exist Plugin Flag
	if ( isset( $assoc_args['plugin'] ) ) {
		if ( empty( $assoc_args['plugin'] ) ) {
			\WP_CLI_APP\Utility\CLI::error( "Please Enter Plugin folder or plugin name." );
		}

		$plugin_check = WorkSpace::search_plugins_or_themes( $assoc_args['plugin'], "plugin" );
		if ( $plugin_check != false ) {
			$workspace = $plugin_check;
			update_option( $GLOBALS['WP-CLI-APP']['workspace'], array(
				"type"  => $workspace['type'],
				"value" => $assoc_args['plugin'],
			) );

			WP_CLI::line( "\n" );
			WP_CLI::success( "The WorkSpace Was Set." );
			WorkSpace::workspace_info_table();
		} else {
			WP_CLI::error( "This Plugin is Not Exists in Your Wordpress Application" );
		}
	} elseif ( isset( $assoc_args['theme'] ) ) {
		//IF Exist theme Flag
		if ( empty( $assoc_args['theme'] ) ) {
			\WP_CLI_APP\Utility\CLI::error( "Please Enter Theme folder or theme name." );
		}

		$theme_check = WorkSpace::search_plugins_or_themes( $assoc_args['theme'], "theme" );
		if ( $theme_check != false ) {
			$workspace = $theme_check;

			update_option( $GLOBALS['WP_CLI_APP']['workspace'], array(
				"type"  => $workspace['type'],
				"value" => $assoc_args['theme'],
			) );

			WP_CLI::line( "\n" );
			WP_CLI::success( "The WorkSpace Was Set." );
			WorkSpace::workspace_info_table();
		} else {
			WP_CLI::error( "This Theme is Not Exists in Your Wordpress Application" );
		}
	} else {
		//if Not find plugin or theme Flag
		\WP_CLI::log( "\n" . WP_CLI::colorize( "%rerror:%n " ) . "For Set Workspace, Use this Command :" );
		WorkSpace::set_workspace_help();
	}
}