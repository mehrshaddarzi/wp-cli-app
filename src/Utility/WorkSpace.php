<?php

namespace WP_CLI_APP\Utility;

use WP_CLI;
use WP_CLI_APP\Package\Arguments\Plugins;
use WP_CLI_APP\Package\Arguments\Themes;

class WorkSpace {

	/**
	 * Get WorkSpace option
	 */
	public static function get_workspace_opt() {
		global $WP_CLI_APP;

		//Check Workspace Option
		$opt = Wordpress::get_option( $WP_CLI_APP['workspace'] );

		//Check Isset Option
		if ( ! $opt ) {
			/**
			 * type -> plugin or theme
			 */
			update_option( $WP_CLI_APP['workspace'], array( "type" => '', "value" => '' ), 'no' );
			return false;
		}

		//Check Empty WorkSpace
		if ( empty( $opt ) ) {
			return false;
		}

		//Check args
		if ( ! isset( $opt['type'] ) || ! isset( $opt['value'] ) ) {
			return false;
		}

		//Check Empty type
		if ( $opt['type'] == "" ) {
			return false;
		}

		return $opt;
	}

	/**
	 * Check Workspace in cli
	 */
	public static function is_active_workspace() {

		//Get current Workspace
		$workspace = self::get_workspace();
		if ( $workspace === false ) {

			//Show Error if WorkSpace is no define
			CLI::log( "\n" . CLI::color( "error:", 'r' ) . "it seems Workspace folder not found or not set, Use this Command :" );
			self::set_workspace_help();
			exit();
		}
	}

	/**
	 * Get Type Of WorkSpace
	 */
	public static function get_workspace() {

		$workspace = self::get_workspace_opt();
		if ( $workspace === false ) {
			return false;
		}
		if ( isset( $workspace['value'] ) and trim( $workspace['value'] ) != "" ) {
			$string = $workspace['value'];
		} else {
			return false;
		}

		//Search item
		$search = self::search_plugins_or_themes( $string, $workspace['type'] );

		//Set Correct Path
		$path = self::get_path_dir( $search );
		array_merge( $search, array( "path" => $path ) );

		return $search;
	}

	/**
	 * Check Valid workspace
	 *
	 * @param bool $log
	 * @return bool|int
	 */
	public static function check_valid_workspace( $log = true ) {

		//Get WorkSpace information
		$workspace = self::get_workspace();
		if ( $workspace === false ) {
			return false;
		}

		//Check config File
		if ( ! file_exists( rtrim( $workspace['path'], "/" ) . '/' . $GLOBALS['WP_CLI_APP']['config_file'] ) ) {
			if ( $log === true ) {
				CLI::error( "Your Workspace is not valid for accept This command. read more ..." );
			} else {
				return false;
			}
		} else {
			return $workspace;
		}
	}

	/**
	 * Set Workspace Help User
	 */
	public static function set_workspace_help() {
		$items = array(
			array(
				'workspace'   => 'Plugin',
				'command'     => 'wp app set:workspace --plugin=[ID]',
				'description' => 'ID is Plugin Name or Plugin Folder name',
			),
			array(
				'workspace'   => 'Theme',
				'command'     => 'wp app set:workspace --theme=[ID]',
				'description' => 'ID is Template Name or Template Folder name',
			),
		);
		CLI::create_table( $items, array( 'workspace', 'command', 'description' ) );
	}

	/**
	 * Get Path dir Of WorkSpace
	 *
	 * @param $workspace
	 * @return mixed|string
	 */
	public static function get_path_dir( $workspace ) {
		if ( is_array( $workspace ) ) {
			$path = $workspace['path'];
		} else {
			$path = $workspace;
		}

		//Check Folder Or File exist
		if ( is_dir( $path ) ) {
			$dir_path = $path;
		} else {
			$dir_path = dirname( $path );
		}

		return $dir_path;
	}

	/**
	 * Show information this workspace
	 *
	 * @param $workspace
	 */
	public static function workspace_info_table( $workspace = false ) {

		//Get current Workspace if information not access
		if ( $workspace === false ) {
			$workspace = self::get_workspace();
		}

		$items = array(
			array(
				'Type'    => ucfirst( $workspace['type'] ),
				'Name'    => $workspace['name'],
				'Version' => $workspace['version'],
			),
		);
		CLI::create_table( $items, array( 'Type', 'Name', 'Version' ) );
	}

	/**
	 * Search Between Plugin Or Theme
	 *
	 * @param $word
	 * @param string $type
	 *
	 * @return array|bool
	 * @package wp-cli-aplication
	 */
	public static function search_plugins_or_themes( $word, $type = 'plugin' ) {

		//First Search in Plugins List by name Or folder name
		if ( $type == "plugin" ) {
			$search_in_plugins = Plugins::search_wordpress_plugins( array(
				'type'      => 'search',
				'search_by' => 'all',
				'search'    => $word
			) );
			if ( $search_in_plugins != false ) {
				return array_merge( array( "type" => "plugin" ), $search_in_plugins );
			}

			return false;
		}

		//First Search in themes List by name Or dir name
		if ( $type == "theme" ) {

			$search_in_themes = Themes::search_wordpress_themes( array(
				'type'      => 'search',
				'search_by' => 'all',
				'search'    => $word
			) );
			if ( $search_in_themes != false ) {
				return array_merge( array( "type" => "theme" ), $search_in_themes );
			}

			return false;
		}

		return false;
	}

	/**
	 * Create Config Json Files
	 *
	 * @param $workspace_path
	 * @param $array
	 * @return bool
	 */
	public static function create_config_file( $workspace_path, $array ) {

		//Data to Json
		$json = json_encode( $array, JSON_PRETTY_PRINT );

		//File put content
		if ( FileSystem::file_put_content( rtrim( $workspace_path, "/" ) . '/' . $GLOBALS['WP_CLI_APP']['config_file'], $json ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Workspace config
	 *
	 * @param bool $key
	 * @return bool|mixed
	 */
	public static function get_workspace_config( $key = false ) {

		//Get WorkSpace information
		$workspace = self::get_workspace();
		if ( $workspace === false ) {
			return false;
		}
		$path = self::get_path_dir( $workspace );

		//Get Data To Json
		$string = file_get_contents( rtrim( $path, "/" ) . '/' . $GLOBALS['WP_CLI_APP']['config_file'] );
		$data   = json_decode( $string, true );

		//Check Export Data
		if ( ! $key ) {
			return $data;
		} else {
			if ( array_key_exists( $key, $data ) ) {
				return $data[ $key ];
			} else {
				return false;
			}
		}
	}


}