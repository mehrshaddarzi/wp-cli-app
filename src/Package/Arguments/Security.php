<?php

namespace WP_CLI_APP\Package\Arguments;

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Package\Utility\install;

class Security {
	/**
	 * Security File to Remove
	 *
	 * @var array
	 */
	public static $security_file = array( 'wp-config-sample.php', 'license.txt', 'readme.html', '.maintenance' );

	/**
	 * Add Mu-Plugins for Security WordPress Package
	 *
	 * @param $pkg_array
	 * @param bool $log
	 */
	public static function wordpress_package_security_plugin( $pkg_array, $log = false ) {

		//get mu-plugins path
		$mu_plugins_path = FileSystem::normalize_path( Dir::eval_get_mu_plugins_path() );
		if ( ! empty( $mu_plugins_path ) ) {
			if ( $log ) {
				CLI::pl_wait_start();
			}

			//Upload Mu-Plugins
			$mustache      = FileSystem::load_mustache();
			$htaccess_code = $mustache->render( 'mu-plugins/access-package' );
			FileSystem::file_put_content(
				FileSystem::path_join( $mu_plugins_path, 'wordpress-package.php' ),
				$mustache->render( 'mu-plugins/wordpress-package', array(
					'code' => $htaccess_code
				) )
			);

			//Added Code to htaccess
			$htaccess = PHP::getcwd( ".htaccess" );
			if ( self::iis7_supports_permalinks( $pkg_array ) === false || file_exists( $htaccess ) ) {
				$file_content = $htaccess_code;
				if ( file_exists( $htaccess ) ) {
					$file_content = @file_get_contents( $htaccess );
					$file_content .= "\n" . $htaccess_code;
				}

				FileSystem::file_put_content( $htaccess, $file_content );
			}

			//log
			if ( $log ) {
				install::add_detail_log( CLI::_e( 'package', 'sec_mu_plugins', array( "[file]" => $GLOBALS['WP_CLI_APP']['package']['file'] ) ) );
				CLI::pl_wait_end();
			}
		}

	}

	/**
	 * Remove Security File
	 *
	 * @param bool $log
	 */
	public static function remove_security_file( $log = false ) {

		// Remove Files For Security
		foreach ( self::$security_file as $file ) {

			//Check Exist File
			$file_path = FileSystem::path_join( PHP::getcwd(), $file );
			if ( file_exists( $file_path ) ) {

				//Remove File
				FileSystem::remove_file( $file_path );

				//Add Log
				if ( $log ) {
					install::add_detail_log( CLI::_e( 'package', 'removed_file', array( "[file]" => $file ) ) );
				}
			}
		}

	}

	/**
	 * Check Support iis permalink
	 *
	 * @param $pkg_array
	 * @return bool
	 */
	public static function iis7_supports_permalinks( $pkg_array ) {
		$iis7_supports_permalinks = false;

		//Upload Plugin file
		$mustache        = FileSystem::load_mustache();
		$mu_plugins_path = Dir::eval_get_mu_plugins_path();
		$get_key         = strtolower( FileSystem::random_key( 80, false ) );
		$data            = array(
			'GET_KEY'   => $get_key,
			'file_name' => 'pretty-permalinks.php',
		);
		$text            = $mustache->render( 'mu-plugins/pretty-permalinks', $data );
		FileSystem::file_put_content( FileSystem::path_join( $mu_plugins_path, 'pretty-permalinks.php' ), $text );

		//Connect to WordPress
		$url     = $pkg_array['config']['site']['url'];
		$request = CLI::http_request( rtrim( $url, "/" ) . "/?wp_cli_iis7_check=" . $get_key );
		if ( $request != false ) {
			if ( isset( $request['is_iis7'] ) and $request['is_iis7'] == "true" ) {
				$iis7_supports_permalinks = true;
			}
		}

		return $iis7_supports_permalinks;
	}
}