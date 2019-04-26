<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\List_Format;
use WP_CLI_APP\Utility\Plugin;

/**
 * Get List Of Plugin Template Starter
 *
 * [--f=<format>]
 * : Render output in a particular format.
 * ---
 * default: table
 * options:
 *   - table
 *   - csv
 *   - json
 *   - count
 *   - yaml
 * ---
 *
 * [--s=<Save_Format>]
 * : Save format File
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app list:plugin-template
 *       Show List of Plugin Template
 *
 *     $ wp app list:plugin-template --f=json
 *       Show List of Plugin Template in json Type
 *
 *     $ wp app list:plugin-template --s=json
 *       Save Json file from Plugin template list
 *
 */
function wp_cli_app_list_plugin_template( $args, $assoc_args ) {

	//Create New Object Plugin Helper
	$plugin = new Plugin();

	//Check if Get List
	$list_template = $plugin->get_list_template();
	$i             = 0;
	$list          = array();
	foreach ( $list_template as $key => $val ) {
		if ( ! empty( $val['link'] ) ) {
			$list[ $i ] = array(
				'name'       => $key,
				'title'      => $val['name'],
				'author_url' => $val['author_url'],
				'desc'       => $val['description'],
				'version'    => $val['version'],
			);
			$i ++;
		}
	}

	//Check if Save Type
	if ( isset( $assoc_args['s'] ) ) {
		$list_format = new List_Format();
		$list_format->run( $list, 'plugin-template', trim( $assoc_args['s'] ) );
	} else {
		//Show List
		CLI::br();
		CLI::format_items( $assoc_args['f'], $list, false );
		CLI::br();
	}
}