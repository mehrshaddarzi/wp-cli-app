<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Git;

/**
 * Git clone command For Wordpress Cli
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app clone:plugin https://github.com.../repo.git
 *
 */
function wp_cli_app_basic_clone( $args, $assoc_args ) {
	CLI::log( "For Clone Project, Use This command:", true );
	Git::help_clone_command();
	return;
}