<?php

use WP_CLI_APP\Package\Arguments\Locale;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\Wordpress;

/**
 * Create a new Wordpress Application
 *
 *
 * [--name=<site-name>]
 * : Wordpress Site Name
 *
 * [--url=<SiteUrl>]
 * : Wordpress Site Url
 *
 * [--admin_user=<AdminUser>]
 * : Admin User
 *
 * [--admin_email=<AdminEmail>]
 * : Admin Email
 *
 * [--admin_pass=<AdminPassword>]
 * : Admin Password
 *
 * [--dbhost=<MysqlServer--localhost>]
 * : Database Host Server Host Name : localhost, 127.0.0.1
 *
 * [--dbuser=<DatabaseUsername--root>]
 * : Database UserName
 *
 * [--dbpass=<DatabasePassword>]
 * : Mysql Server Password
 *
 * [--dbname=<DatabaseName>]
 * : Database Name
 *
 * [--dbprefix=<TablePrefix--wp_>]
 * : Database Prefix Table
 *
 * [--locale=<Language--en_US>]
 * : Wordpress Language
 *
 * [--wp_content_dir=<wp-content>]
 * : Wp-content Folder name
 *
 * [--plugins_dir=<plugins>]
 * : Plugins dir folder name
 *
 * [--uploads_dir=<uploads>]
 * : Uploads Folder Name
 *
 * [--themes_dir=<themes>]
 * : Themes dir folder name
 *
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/
 *
 * ## EXAMPLES
 *
 *     $ wp app create
 *     Create a Wordpress Application
 *
 *
 * @when before_wp_load
 */
function wp_cli_app_basic_create( $args, $assoc_args ) {

	/** Force Only run With --Prompt */
	if ( ! isset ( $assoc_args['prompt'] ) and count( $assoc_args ) == 0 ) {
		WP_CLI::runcommand( "app create --prompt" );

		return;
	}


	/** Check Exist Wordpress */
	if ( Wordpress::check_wp_exist() ) {
		WP_CLI::log( "error: WordPress files seem to already be present here." );

		return;
	}


	/** Check Structure Folders */
	$change_folder = false;

	//check wp-content folder name
	$wp_content_folder_name = FileSystem::sanitize_folder_name( WP_CLI\Utils\get_flag_value( $assoc_args, 'wp_content_dir', 'wp-content' ) );
	if ( $wp_content_folder_name != "wp-content" ) {
		$change_folder     = true;
		$change_wp_content = true;
	}

	//Check Plugins folder
	$plugins_dir_name = FileSystem::sanitize_folder_name( WP_CLI\Utils\get_flag_value( $assoc_args, 'plugins_dir', 'plugins' ) );
	if ( $plugins_dir_name != "plugins" ) {
		$change_folder         = true;
		$change_plugins_folder = true;
	}

	//Check Themes folder
	$themes_dir_name = FileSystem::sanitize_folder_name( WP_CLI\Utils\get_flag_value( $assoc_args, 'themes_dir', 'themes' ) );
	if ( $themes_dir_name != "themes" ) {
		$change_themes_folder = true;
	}

	//check uploads Folder
	$uploads_folder_name = FileSystem::sanitize_folder_name( WP_CLI\Utils\get_flag_value( $assoc_args, 'uploads_dir', 'uploads' ) );
	if ( $uploads_folder_name != "uploads" ) {
		$change_folder         = true;
		$change_uploads_folder = true;
	}

	//number Step install
	$step = 6;
	if ( ( $change_folder === true ) || ( isset( $change_themes_folder ) and $change_themes_folder === true ) ) {
		$step = 7;
	}


	/** Create Empty MySql Database */
	$dbhost   = WP_CLI\Utils\get_flag_value( $assoc_args, 'dbhost', 'localhost' );
	$dbuser   = WP_CLI\Utils\get_flag_value( $assoc_args, 'dbuser', 'root' );
	$dbpass   = WP_CLI\Utils\get_flag_value( $assoc_args, 'dbpass', '' );
	$dbname   = WP_CLI\Utils\get_flag_value( $assoc_args, 'dbname', 'wordpress' );
	$dbprefix = WP_CLI\Utils\get_flag_value( $assoc_args, 'dbprefix', 'wp_' );
	\WP_CLI_APP\Utility\DB::create_wp_db( array(
		'dbhost' => $dbhost,
		'dbuser' => $dbuser,
		'dbpass' => $dbpass,
		'dbname' => $dbname
	) );
	WP_CLI::log( "installation 1/" . $step . " : `" . $dbname . "` Database is Created Successfully" );


	/** Download Core Version Wordpress $options */
	$options = array(
		'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
		'parse'      => 'json', // Parse captured STDOUT to JSON array.
		'launch'     => true,  // Reuse the current process.
		'exit_error' => true,   // Halt script execution on error.
	);
	WP_CLI::runcommand( "core download", $options );
	WP_CLI::log( "installation 2/" . $step . " : Wordpress is Download Successfully" );


	/** Create Config.php file */
	WP_CLI::runcommand( "config create --dbhost=" . $dbhost . " --dbname=" . $dbname . " --dbuser=" . $dbuser . " --dbpass=" . $dbpass . " --dbprefix=" . $dbprefix, $options );
	$config_transformer = new WPConfigTransformer( getcwd() . '/wp-config.php' );
	$config_transformer->add( 'constant', 'WP_DEBUG', 'false', array( 'raw' => true, 'normalize' => true ) );
	WP_CLI::log( "installation 3/" . $step . " : wp-config.php file is Created Successfully" );


	/** Remove wp-config-sample.php and readme.html for Security */
	@unlink( getcwd() . '/wp-config-sample.php' );
	@unlink( getcwd() . '/license.txt' );
	@unlink( getcwd() . '/readme.html' );
	WP_CLI::log( "installation 4/" . $step . " : Remove wp-config-sample.php and readme File For Security" );


	/** install Wordpress */
	if ( isset( $assoc_args['url'] ) ) {
		WP_CLI::set_url( $assoc_args['url'] );
	}
	$sitename    = WP_CLI\Utils\get_flag_value( $assoc_args, 'name', 'blog' );
	$admin_user  = WP_CLI\Utils\get_flag_value( $assoc_args, 'admin_user', 'admin' );
	$admin_pass  = WP_CLI\Utils\get_flag_value( $assoc_args, 'admin_pass', 'admin' );
	$admin_email = WP_CLI\Utils\get_flag_value( $assoc_args, 'admin_email', 'info@example.com' );
	if ( ! filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
		$admin_email = "info@example.com";
	}
	WP_CLI::runcommand( "core install --url=" . $assoc_args['url'] . " --title=" . $sitename . " --admin_user=" . $admin_user . " --admin_password=" . $admin_pass . " --admin_email=" . $admin_email . "", $options );
	WP_CLI::log( "installation 5/" . $step . " : install Wordpress And Create Table in Database" );


	/** Set Language */
	$locale = WP_CLI\Utils\get_flag_value( $assoc_args, 'locale', 'en_US' );
	if ( $locale != "en_US" ) {
		WP_CLI::runcommand( "language core install " . $locale, $options );
		WP_CLI::runcommand( "site switch-language " . $locale, $options );
	}
	$country = Locale::get_wordpress_locale( trim( $args[0] ) );
	if ( $country['status'] === true ) {
		$country = $country['data'] . " Country ";
	}
	WP_CLI::log( "installation 6/" . $step . " : " . $country . "Language [" . $locale . "] is Setup." );


	/** Generate Standard Home Url */
	$url = filter_var( $assoc_args['url'], FILTER_SANITIZE_URL );
	if ( $ret = parse_url( $assoc_args['url'] ) ) {
		if ( ! isset( $ret["scheme"] ) ) {
			$url = "http://{$assoc_args['url']}";
		}
	}

	//Set Url Define
	if ( $change_folder === true ) {
		$config_transformer->add( 'constant', 'WP_HOME', rtrim( $url, "/" ), array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_SITEURL', rtrim( $url, "/" ), array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_CONTENT_FOLDER', $wp_content_folder_name, array( 'raw' => false, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_CONTENT_DIR', 'ABSPATH . WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_CONTENT_URL', 'WP_SITEURL.\'/\'.WP_CONTENT_FOLDER', array( 'raw' => true, 'normalize' => true ) );
	}

	//Set Config folder
	if ( isset( $change_wp_content ) ) {
		rename( getcwd() . '/wp-content', getcwd() . '/' . $wp_content_folder_name );
	}

	//Set plugins folder
	if ( isset( $change_plugins_folder ) ) {
		$config_transformer->add( 'constant', 'WP_PLUGIN_DIR', "WP_CONTENT_DIR . '/" . $plugins_dir_name . "'", array( 'raw' => true, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'PLUGINDIR', "WP_CONTENT_DIR . '/" . $plugins_dir_name . "'", array( 'raw' => true, 'normalize' => true ) );
		$config_transformer->add( 'constant', 'WP_PLUGIN_URL', "WP_CONTENT_URL.'/" . $plugins_dir_name . "'", array( 'raw' => true, 'normalize' => true ) );
		rename( getcwd() . '/' . $wp_content_folder_name . '/plugins', getcwd() . '/' . $wp_content_folder_name . '/' . $plugins_dir_name );
	}

	//set uploads Folder
	if ( isset( $change_uploads_folder ) ) {
		if ( isset( $change_wp_content ) and $change_wp_content === true ) {
			$uploads_folder_name = "WP_CONTENT_FOLDER . '/" . $uploads_folder_name . "'";
		} else {
			$uploads_folder_name = "wp-content/'.$uploads_folder_name.'";
		}
		$config_transformer->add( 'constant', 'UPLOADS', $uploads_folder_name, array( 'raw' => false, 'normalize' => true ) );
		rename( getcwd() . '/' . $wp_content_folder_name . '/uploads', getcwd() . '/' . $wp_content_folder_name . '/' . $uploads_folder_name );
	}

	//set themes Folder
	if ( isset( $change_themes_folder ) ) {
		rename( getcwd() . '/' . $wp_content_folder_name . '/themes', getcwd() . '/' . $wp_content_folder_name . '/' . $themes_dir_name );
		$mustache = FileSystem::load_mustache();
		FileSystem::file_put_content( getcwd() . "/" . $wp_content_folder_name . "/mu-plugins/theme-dir.php", $mustache->render( 'mu-plugins/theme-dir', array( 'dir' => $themes_dir_name ) ) );
	}

	//Show Log change structure folder
	if ( ( $change_folder === true ) || ( isset( $change_themes_folder ) and $change_themes_folder === true ) ) {
		WP_CLI::log( "installation 7/" . $step . " : Folder name changed." );
	}

	WP_CLI::log( "Success : Wordpress is installed successfully." );
	WP_CLI::log( "\n----------\nAdmin Url : " . rtrim( $url, "/" ) . "/wp-login.php\nUserName: " . $admin_user . "\nPassword: " . $admin_pass . "\n----------" );
}