<?php

namespace WP_CLI_APP\Package\Arguments;

use WP_CLI_APP\Package\Utility\install;
use WP_CLI_APP\Package\Utility\temp;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\WP_CLI_ERROR;

class Version {
	/**
	 * Download WordPress
	 *
	 * @param $version
	 */
	public static function download_wordpress( $version = 'latest' ) {
		$cmd = "core download --version=%s --force";
		CLI::run_command( \WP_CLI\Utils\esc_cmd( $cmd, $version ) );
	}

	/**
	 * Update WordPress Command
	 *
	 * @param string $version
	 * @param string $locale
	 */
	public static function update_wordpress_cmd( $version = 'latest', $locale = 'en_US' ) {
		$cmd = "core update --version=%s --locale=%s --force";
		CLI::run_command( \WP_CLI\Utils\esc_cmd( $cmd, $version, $locale ) );
		CLI::run_command( "option delete core_updater.lock", array( 'exit_error' => false ) );
	}

	/**
	 * Get log download WordPress
	 *
	 * @param $version
	 * @param string $locale
	 * @return string
	 */
	public static function get_log_download_wordpress( $version, $locale = 'en_US' ) {

		//Convert latest to version
		$version = ( $version == "latest" ? self::get_latest_version_num_wordpress() : $version );

		//Check File name
		$file_name = "wordpress-{$version}-{$locale}.[extension]";

		//Check exist File
		$exist = false;
		foreach ( array( 'zip', 'tar.gz' ) as $ext ) {
			$file = str_replace( "[extension]", $ext, $file_name );
			if ( CLI::exist_cache_file( "/core/" . $file ) != false ) {
				$exist = true;
				break;
			}
		}

		//show log
		if ( $exist === false ) {
			return CLI::_e( 'package', 'get_wp', array( '[run]' => "Download", '[version]' => "v" . $version ) );
		} else {
			return CLI::_e( 'package', 'get_wp', array( '[run]' => "Copy", '[version]' => "v" . $version ) );
		}
	}

	/**
	 * Check Wordpress Download Url in custom versions and locale.
	 * We use Core_Command/get_download_url method.
	 *
	 * @param $version
	 * @param string $locale
	 * @param string $file_type
	 * @return array
	 */
	public static function check_download_url( $version, $locale = 'en_US', $file_type = 'zip' ) {

		//Create Object Validation
		$valid = new WP_CLI_ERROR();

		//Check nightly Version
		if ( 'nightly' === $version && 'en_US' !== $locale ) {
			$valid->add_error( CLI::_e( 'package', 'er_nightly_ver' ) );
		}

		//Prepare Download Link
		if ( 'en_US' === $locale ) {
			$url = 'https://wordpress.org/wordpress-' . $version . '.' . $file_type;
		} else {
			$url = sprintf(
				'https://%s.wordpress.org/wordpress-%s-%s.' . $file_type,
				substr( $locale, 0, 2 ),
				$version,
				$locale
			);
		}

		//Check Wordpress download url cache
		$file_path = get_wp_cli_app_config( 'package', 'wordpress_core_url_file' );

		//Check Cache File exist
		if ( file_exists( $file_path ) ) {

			//Get Json data
			$json_data = FileSystem::read_json_file( $file_path );

			//Check Url in List
			if ( in_array( $url, $json_data ) and ! $valid->is_cli_error() ) {
				return $valid->result();
			}
		}

		//Check Exist Download Url
		$exist_url = PHP::exist_url( $url, "core", false );
		if ( $exist_url['status'] === false ) {
			if ( isset( $exist_url['error_type'] ) and $exist_url['error_type'] == "base" ) {
				$valid->add_error( $exist_url['data'] );
			} else {
				$valid->add_error( CLI::_e( 'package', 'er_found_release' ) );
			}
		}

		//Save to file if all status is ok
		if ( ! $valid->is_cli_error() ) {

			//Add Url To list
			$json_data[] = $url;

			//Push To file
			FileSystem::create_json_file( $file_path, $json_data, false );
		}

		return $valid->result();
	}

	/**
	 * Get List Wordpress Version
	 *
	 * @param bool $version
	 * @param bool $force_update
	 * @return array|bool
	 */
	public static function get_wordpress_version( $version = false, $force_update = false ) {

		//Cache File name for wordpress version
		$file_path = get_wp_cli_app_config( 'package', 'version', 'file' );

		//Check Cache File exist
		if ( file_exists( $file_path ) ) {

			//if cache file exist we used same file
			$json_data = FileSystem::read_json_file( $file_path );
		}

		// if Force Update
		if ( $force_update === false ) {

			//if require update by calculate cache time
			if ( isset( $json_data ) and FileSystem::check_file_age( $file_path, get_wp_cli_app_config( 'package', 'version', 'age' ) ) === false ) {
				$list = $json_data;
			}
		}

		//Fetch Versions List
		if ( ! isset( $list ) || $force_update === true ) {

			//Get Wordpress Version From API
			$versions_list = self::fetch_wordpress_versions();
			if ( $versions_list['status'] === false ) {
				if ( ! isset( $json_data ) ) {
					return $versions_list;
				} else {
					$list = $json_data;
				}
			} else {
				$list = $versions_list['data'];
			}
		}

		//Check Version number
		if ( isset( $list ) and $list != false ) {

			//Get All List
			if ( $version === false ) {
				return array( 'status' => true, 'data' => $list );
			} else {
				if ( array_key_exists( $version, $list ) ) {
					return array( 'status' => true );
				}
			}
		}

		return array( 'status' => false, 'data' => CLI::_e( 'package', 'version_exist' ) );
	}

	/**
	 * Get Wordpress Version List From WordPress.org API
	 */
	public static function fetch_wordpress_versions() {

		//Cache File name for wordpress version
		$version_list = get_wp_cli_app_config( 'package', 'version', 'file' );

		//Connect To Wordpress API
		$list = CLI::http_request( get_wp_cli_app_config( 'wordpress_api', 'version' ) );
		if ( $list != false ) {

			//convert list to json file
			$list = json_decode( $list, true );

			//Create Cache file for wordpress version list
			FileSystem::create_json_file( $version_list, $list, false );
		} else {

			//Show Error connect to WP API
			return array( 'status' => false, 'data' => CLI::_e( 'wordpress_api', 'connect' ) );
		}

		return array( 'status' => true, 'data' => $list );
	}

	/**
	 * Get Last Version of WordPress
	 */
	public static function get_latest_version_num_wordpress() {
		$version_list = self::get_wordpress_version();
		$latest       = 'latest';
		if ( $version_list['status'] ) {
			foreach ( $version_list['data'] as $version => $status ) {
				if ( $status == "latest" ) {
					$latest = $version;
				}
			}
		}

		return $latest;
	}

	/**
	 * Update Version WordPress Core in Package
	 *
	 * @param $pkg
	 */
	public static function update_version( $pkg ) {

		//Get Local Temp
		$localTemp = temp::get_temp( PHP::getcwd() );
		$tmp       = ( $localTemp === false ? array() : $localTemp );

		// Get Latest Version of WordPress
		$latest_wp_version = self::get_latest_version_num_wordpress();

		// Check Tmp Version
		$tmp_version = ( isset( $tmp['core']['version'] ) ? $tmp['core']['version'] : get_bloginfo( 'version' ) );
		$tmp_version = ( $tmp_version == "latest" ? $latest_wp_version : $tmp_version );

		// Check Pkg version
		$pkg_version = ( isset( $pkg['core']['version'] ) ? $pkg['core']['version'] : '1.0.0' );
		$pkg_version = ( $pkg_version == "latest" ? $latest_wp_version : $pkg_version );

		// Check if Changed
		if ( $tmp_version != $pkg_version ) {

			//Show Please wait
			CLI::pl_wait_start();

			// Update WordPress core
			self::update_wordpress_cmd( $pkg_version );

			// Remove Security File again
			Security::remove_security_file();

			// Remove Pls wait
			CLI::pl_wait_end();

			// Add log
			install::add_detail_log( rtrim( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Changed", "[key]" => "WordPress Version", "[type]" => "to " . $pkg_version . "" ) ), "." ) );
		}
	}

}