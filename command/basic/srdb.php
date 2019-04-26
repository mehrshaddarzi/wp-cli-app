<?php

use WP_CLI_APP\Utility\CLI;

/**
 * Searches/replaces strings in the database
 *
 * <old>
 * : A string to search for within the database.
 *
 * <new>
 * : Replace instances of the first string with this new string.
 *
 * [<table>]
 * : List of database tables to restrict the replacement to. Wildcards are supported, e.g. 'wp_*options' or 'wp_post*'.
 *
 * [--dry-run]
 * : Run the entire search/replace operation and show report, but don’t save changes to the database.
 *
 * [--network]
 * : Search/replace through all the tables registered to $wpdb in a multiSite install.
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app srdb http://old.com http://new.com
 *      Search and Replace old.com to new.com in database
 *
 *      $ wp app srdb old new --dry-run
 *      remove of all draft posts
 *
 * @param $args
 * @param $assoc_args
 */
function wp_cli_app_basic_srdb( $args, $assoc_args ) {

	//Base Command
	//@see https://developer.wordpress.org/cli/commands/search-replace/
	$command = "search-replace {$args[0]} {$args[1]}";

	//Check Exist table
	if ( isset( $args[2] ) ) {
		$command .= " {$args[2]}";
	}

	//Check Dry-run Or network
	foreach (array("dry-run", "network") as $assoc) {
		if ( isset( $assoc_args[$assoc] ) ) {
			$command .= " --{$assoc}";
		}
	}

	//Run
	CLI::run( $command, true );
}