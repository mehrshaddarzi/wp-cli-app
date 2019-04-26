<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Plugin;

/**
 * Create New Wordpress Plugin
 *
 * [--template_name=<app>]
 * : Plugin Template Name
 *
 * [--plugin_name=<Plugin_Name>]
 * : Plugin Name
 *
 * [--plugin_slug=<plugin_slug>]
 * : Plugin Slug
 *
 * [--plugin_description=<plugin_description>]
 * : Plugin Description
 *
 * [--plugin_url=<plugin_url>]
 * : Plugin Url
 *
 * [--author_name=<Author_name>]
 * : Author Name
 *
 * [--author_url=<Author_url>]
 * : Author Url
 *
 * [--text_domain=<Text_domain>]
 * : Plugin Text domain
 *
 * [--namespace=<php_namepsace>]
 * : Plugin Namespace
 *
 * [--active_plugin]
 * : do you Want Active this Plugin after Setup completely ?
 *
 * [--set_workspace]
 * : do you Want Set Current WorkSpace this Plugin after Setup completely ?
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app make:plugin
 *       Create New Plugin with Default `app` Template
 *
 *
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_make_plugin( $args, $assoc_args ) {
	global $WP_CLI_APP;

	/** Force Only run With --Prompt */
	if ( ! isset ( $assoc_args['prompt'] ) and count( $assoc_args ) == 0 ) {
		WP_CLI::runcommand( "app make:plugin --prompt" );

		return;
	}

	//Create New Object Plugin Helper
	$plugin = new Plugin();

	//Check Plugin Template
	$plugin_template = CLI::get_flag_value( $assoc_args, 'template_name', $WP_CLI_APP['default_plugin_template'] );
	if ( $plugin->search_template( $plugin_template ) === false ) {
		CLI::error( "Plugin Template with name `{$plugin_template}` is not Found. Show All Access List use `wp app list:plugin-template`" );
	}

	//Create Plugin
	$plugin->create( $plugin_template, $assoc_args );

	//Success
	CLI::success( "The Plugin is Setup completely." );
}