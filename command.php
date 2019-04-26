<?php

namespace WP_CLI_APP;

# Check exist WordPress command line
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

# Define Basic WP-CLI APPLICATION Constant
define( "WP_CLI_APP_PATH", dirname( __FILE__ ) );
define( "WP_CLI_APP_HOME_PATH", Utility\CLI::get_home_path( 'application' ) );
define( "WP_CLI_APP_CACHE_PATH", Utility\CLI::get_cache_dir( 'app' ) );

# Load functions
require_once WP_CLI_APP_PATH . '/functions.php';

# Register 'global-config' Command
\WP_CLI::add_command( 'global-config', Config::class );

# Register 'Reference' Command
//require_once( WP_CLI_APP_PATH . '/src/Reference.php' );

# Register 'init' Command
require_once( WP_CLI_APP_PATH . '/src/init.php' );

# Register 'install' Command
require_once( WP_CLI_APP_PATH . '/src/install.php' );

# Register 'uninstall' Command
require_once( WP_CLI_APP_PATH . '/src/uninstall.php' );

# Register 'Pack' Command
\WP_CLI::add_command( 'pack', Pack::class );

# Register 'explorer' Command
require_once( WP_CLI_APP_PATH . '/src/explorer.php' );

# Register 'tools' Command
\WP_CLI::add_command( 'tools', Tools::class );

/**
 * Basic Usage Application Commands
 *
 * ---
 * List Commands :
 *
 * config    -> Application configuration.
 * init      -> Create a new WordPress package.
 * docs      -> Show Documentation in the web browser.
 * package   -> WordPress package management system
 * install   -> install WordPress Package
 *
 *
 *
 * create    -> Create a new Wordpress Application
 * remove    -> Remove Wordpress Completely.
 * update    -> Update Wordpress Core and All Plugin.
 * reset     -> Reset Database Wordpress Application.
 * composer  -> Using composer php package manager For Workspace.
 * git       -> Using Git control Version command For Workspace.
 * npm       -> Using Npm command For Workspace.
 * backup    -> Backup Database Or files in Wordpress.
 * clean     -> Full Optimize Wordpress.
 * optimize  -> optimize and repair database.
 * workspace -> Show Current workspace.
 * flush     -> flushing Rewrite url and cache in wordpress.
 * run       -> run wordpress website in browser.
 * explorer  -> Explorer Main Folder Wordpress Application.
 * srdb      -> Searches/replaces strings in the database.
 * remote    -> Show/Add Remote FTP server information.
 * codex     -> Search in Wordpress Code Reference.
 *
 * ---
 *
 * $ wp app {key} ....
 *
 */
\WP_CLI::add_command( $GLOBALS['WP_CLI_APP']['command'], App::class );
WP_CLI_APP_LOADER_COMMAND( "basic" );

/**
 * Set Values in Wordpress Application
 *
 * ---
 * List Commands :
 *
 * debug      -> Change debug type Wordpress.
 * timezone   -> Change WordPress Timezone.
 * lang       -> change Language Of Wordpress For Example : fa_IR.
 * workspace  -> set workspace for developer.
 * themes     -> set or change themes dir.
 * url        -> Change Wordpress Site url with replace in all table.
 * wp-content -> change of wp-content Folder in wordpress.
 * plugins    -> change of plugins folder in wordpress.
 * uploads    -> change of uploads folder in wordpress.
 * password   -> change of user password.
 * login      -> Automatic login By user ID Or User_login Or user_email.
 * revisions  -> Limit the number of posts revisions that WordPress stores in the database.
 * memory     -> Increase PHP Memory for Wordpress.
 * cache      -> enable or disabled WP_Cache.
 * cookie     -> Set New Prefix for Wordpress Cookie.
 * db-prefix  -> Rename Wordpress Database Prefix.
 * remote     -> Set FTP Remote For Manage Wordpress Site in Another Server.
 * ---
 *
 * $ wp app set:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "set", ":" );

/**
 * Backup System in Wordpress Application
 *
 * ---
 * List Commands :
 *
 * db         -> Get backup From database sql File.
 * plugins    -> Get backup From plugins folder.
 * themes     -> Get backup From themes folder.
 * uploads    -> Get backup From uploads folder.
 * wp-content -> Get backup From wp-content folder.
 * workspace  -> Get backup From current workspace folder.
 * ---
 *
 * $ wp app backup:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "backup", ":" );

/**
 * WordPress Management via FTP remote.
 *
 * ---
 * List Commands :
 *
 * backup     -> Create backup From Wordpress database Or Files.
 * login      -> Automatic login By user ID Or User_login Or user_email.
 * ---
 *
 * $ wp app remote:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "remote", ":" );

/**
 * Make a New Component in Wordpress Application
 *
 * ---
 * List Commands :
 *
 * htaccess -> Create Htaccess or Web.config For Pretty Permalink WordPress.
 * Salt     -> Refreshes the salts defined.
 * plugin   -> Create New Wordpress Plugin.
 * theme    -> Create New Wordpress theme
 * ---
 *
 * $ wp app make:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "make", ":" );

/**
 * Get List Of information about Users, Post , ....
 *
 * ---
 * List Commands :
 *
 * locale            -> Get List Of Wordpress locale.
 * version           -> Get List Of Wordpress Version.
 * timezone          -> Get List Of Wordpress TimeZone.
 * plugin-template   -> Get List Of Plugin Template Starter.
 * remove            -> Remove _list folder.
 * ---
 *
 * $ wp app list:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "list", ":" );

/**
 * Clone Git Project in Wordpress Application
 *
 * ---
 * List Commands :
 *
 * plugin -> clone a project in Plugin dir.
 * theme  -> clone a project in themes dir.
 * ---
 *
 * $ wp app clone:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "clone", ":" );

/**
 * Search in Wordpress Codex Reference
 *
 * ---
 * List Commands :
 *
 * remove   -> Removed Complete Wordpress Codex Cache.
 * show     -> View Reference By ID after search.
 * function -> Search in Wordpress Reference functions.
 * hook     -> Search in Wordpress Reference Hooks.
 * class    -> Search in Wordpress Reference Class.
 * method   -> Search in Wordpress Reference Methods.
 * ---
 *
 * $ wp app codex:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "codex", ":" );


/**
 * Helper function for WordPress Application
 *
 * ---
 * List Commands :
 *
 * exist-option -> Create Htaccess or Web.config For Pretty Permalink WordPress.
 * ---
 *
 * $ wp app make:{key} ....
 *
 */
WP_CLI_APP_LOADER_COMMAND( "tools", ":" );