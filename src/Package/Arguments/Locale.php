<?php

namespace WP_CLI_APP\Package\Arguments;

use WP_CLI_APP\Package\Utility\install;
use WP_CLI_APP\Package\Utility\temp;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;

class Locale {
	/**
	 * Get Locale Detail
	 *
	 * @param $locale
	 * @return string
	 */
	public static function get_locale_detail( $locale ) {
		$return  = '';
		$country = self::get_wordpress_locale( trim( $locale ) );
		if ( $country['status'] === true ) {
			$return = $country['data'];
		}

		return $return;
	}

	/**
	 * Installs a given language.
	 *
	 * @see https://developer.wordpress.org/cli/commands/language/core/install/
	 * @param $locale
	 */
	public static function run_language_install( $locale ) {
		$cmd = "language core install %s";
		CLI::run_command( \WP_CLI\Utils\esc_cmd( $cmd, $locale ) );
	}

	/**
	 * Activates a given language.
	 *
	 * @see https://developer.wordpress.org/cli/commands/site/switch-language/
	 * @param $locale
	 */
	public static function run_switch_language( $locale ) {
		$cmd = "site switch-language %s";
		CLI::run_command( \WP_CLI\Utils\esc_cmd( $cmd, $locale ) );
	}

	/**
	 * Get List Of Available List language in WordPress
	 *
	 * @return bool|mixed
	 */
	public static function get_available_languages() {

		//Get List languages
		$locale = \WP_CLI::runcommand( 'eval "echo json_encode(get_available_languages());"', array( 'return' => 'stdout', 'parse' => 'json' ) );

		//Return
		return array_merge( array( get_wp_cli_app_config( 'package', 'default', 'locale' ) ), $locale );
	}

	/**
	 * install and active language in WordPress
	 *
	 * @param $pkg_array
	 */
	public static function install_lang( $pkg_array ) {
		CLI::pl_wait_start();
		self::run_language_install( $pkg_array['core']['locale'] ); # Download Language
		CLI::pl_wait_end();
		install::add_detail_log( CLI::_e( 'package', 'install_lang', array( "[key]" => $pkg_array['core']['locale'] ) ) );
		self::run_switch_language( $pkg_array['core']['locale'] ); # Switch To Language
		install::add_detail_log( CLI::_e( 'package', 'active_lang', array( "[key]" => $pkg_array['core']['locale'] ) ) );
	}

	/**
	 * Get Wordpress Locale List From WordPress.org API
	 */
	public static function fetch_wordpress_locale() {

		//Cache File name for wordpress locale
		$locale_list = get_wp_cli_app_config( 'package', 'locale', 'file' );

		//Connect To Wordpress API
		$list = CLI::http_request( get_wp_cli_app_config( 'wordpress_api', 'translations' ) );
		if ( $list != false ) {

			//Convert To Json data
			$list = json_decode( $list, true );
			$list = $list['translations'];
			$json = array();
			foreach ( $list as $k => $v ) {
				if ( array_key_exists( 'language', $v ) ) {
					$json[ $v['language'] ] = $v['english_name'];
				}
			}
			$list = $json;

			//Save File to Cache System
			FileSystem::create_json_file( $locale_list, $list, false );
		} else {

			//Show Error connect to WP API
			return array( 'status' => false, 'data' => CLI::_e( 'wordpress_api', 'connect' ) );
		}

		return array( 'status' => true, 'data' => $list );
	}

	/**
	 * Get List Wordpress Locale
	 *
	 * @param bool $key
	 * @param bool $force_update
	 * @return array|bool
	 */
	public static function get_wordpress_locale( $key = false, $force_update = false ) {

		//Cache File name for wordpress locale
		$file_path = get_wp_cli_app_config( 'package', 'locale', 'file' );

		//Check Cache File exist
		if ( file_exists( $file_path ) ) {

			//if cache file exist we used same file
			$json_data = FileSystem::read_json_file( $file_path );
		}

		// if Force Update
		if ( $force_update === false ) {

			//if require update by calculate cache time
			if ( isset( $json_data ) and FileSystem::check_file_age( $file_path, get_wp_cli_app_config( 'package', 'locale', 'age' ) ) === false ) {
				$list = $json_data;
			}
		}

		//Fetch Locale List
		if ( ! isset( $list ) || $force_update === true ) {

			//Get Wordpress Locale From API
			$locale_list = Locale::fetch_wordpress_locale();
			if ( $locale_list['status'] === false ) {
				if ( ! isset( $json_data ) ) {
					return $locale_list;
				} else {
					$list = $json_data;
				}
			} else {
				$list = $locale_list['data'];
			}
		}

		//Check Version number
		if ( isset( $list ) and $list != false ) {

			//Push Default To list if not exist
			$list['en_US'] = 'English';

			//Sort List By Alpha
			ksort( $list );

			//Get All List
			if ( $key === false ) {
				return array( 'status' => true, 'data' => $list );
			} else {
				if ( array_key_exists( $key, $list ) ) {
					return array( 'status' => true, 'data' => $list[ $key ] );
				}
			}
		}

		return array( 'status' => false, 'data' => CLI::_e( 'package', 'wrong_locale' ) );
	}

	/**
	 * Update WordPress language in Package
	 *
	 * @param $pkg
	 */
	public static function update_language( $pkg ) {

		//Get Local Temp
		$localTemp = temp::get_temp( PHP::getcwd() );
		$tmp       = ( $localTemp === false ? array() : $localTemp );

		// Get WordPress default locale
		$default_locale = get_wp_cli_app_config( 'package', 'default', 'locale' );

		// Check Tmp locale
		$tmp_locale = ( isset( $tmp['core']['locale'] ) ? $tmp['core']['locale'] : get_locale() );

		// Check Pkg version
		$pkg_locale = ( isset( $pkg['core']['locale'] ) ? $pkg['core']['locale'] : $default_locale );

		// Check if Changed
		if ( $tmp_locale != $pkg_locale ) {

			//Show Please wait
			CLI::pl_wait_start();

			// Change WordPress Locale
			self::run_language_install( $pkg_locale ); # Download Language
			self::run_switch_language( $pkg_locale ); # Switch To Language

			// Remove Pls wait
			CLI::pl_wait_end();

			// Add log
			$lang = Locale::get_locale_detail( $pkg_locale );
			install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Changed", "[key]" => "WordPress language", "[type]" => "to " . $pkg_locale . ( $lang == "" ? '' : CLI::color( " [" . $lang . "]", "P" ) ) ) ) );
		}
	}

}