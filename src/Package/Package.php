<?php

namespace WP_CLI_APP\Package;

use WP_CLI_APP\Package\Arguments\Plugins;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;

/**
 * WordPress Package System.
 *
 * @package WP_CLI_APP
 * @author  Mehrshad Darzi <mehrshad@wp-cli-app.com>
 * @since   1.0.0
 */
class Package {
	/**
	 * Get Wordpress Package options
	 *
	 * @var string
	 */
	public $package_config;

	/**
	 * Package Config Path
	 *
	 * @var string
	 */
	public $package_path;

	/**
	 * Primary Keys in WordPress Package
	 *
	 * @var array
	 */
	public $primary_keys = array( 'core', 'config', 'mysql' );

	/**
	 * Package constructor.
	 */
	public function __construct() {
		/*
		 * Set global Package config
		 */
		$this->package_config = get_wp_cli_app_config( 'package' );
		/*
		 * Get Full path of Wordpress package File
		 */
		$this->package_path = FileSystem::path_join( PHP::getcwd(), $this->package_config['file'] );
	}

	/**
	 * Check exist wordpress package file
	 */
	public function exist_package_file() {
		if ( file_exists( $this->package_path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Remove Package File
	 */
	public function remove_package_file() {
		if ( file_exists( $this->package_path ) ) {
			FileSystem::remove_file( $this->package_path );
		}
	}

	/**
	 * Read WordPress Package Data
	 */
	public function get_package_data() {

		//Check Exist Package
		if ( $this->exist_package_file() === false ) {
			return array( 'status' => false, 'data' => CLI::_e( 'package', 'not_exist_pkg' ) );
		}

		//Read Json file
		$json_data = FileSystem::read_json_file( $this->package_path );
		if ( $json_data === false ) {
			return array( 'status' => false, 'data' => CLI::_e( 'package', 'er_pkg_syntax' ) );
		}

		return array( 'status' => true, 'data' => $json_data );
	}

	/**
	 * Set Global Setting for Check params in Running Package
	 */
	public function set_global_package_run_check() {

		//Active Run Check MYSQL
		define( 'WP_CLI_APP_PACKAGE_RUN_CHECK_MYSQL', true );

		//Active Run Check WebSite Url
		define( 'WP_CLI_APP_PACKAGE_RUN_CHECK_SITE_URL', true );

		//Active Run Check Custom Url exist
		define( 'WP_CLI_APP_PACKAGE_RUN_EXIST_CUSTOM_URL', true );
	}
}