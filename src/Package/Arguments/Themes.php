<?php

namespace WP_CLI_APP\Package\Arguments;

use WP_CLI_APP\API\WP_Themes_Api;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Package\Utility\install;

class Themes {
	/**
	 * Get Current theme WordPress
	 *
	 * @return mixed
	 */
	public static function eval_get_current_theme() {
		return \WP_CLI::runcommand( 'eval "echo get_template();"', array( 'return' => 'stdout' ) );
	}

	/**
	 * Get WordPress theme list in eval cli [use in install step]
	 */
	public static function eval_themes_list() {
		return \WP_CLI::runcommand( 'eval "$list = array(); foreach(wp_get_themes() as $stylesheet => $v) { $list[] = $stylesheet; } echo json_encode($list);"', array( 'return' => 'stdout', 'parse' => 'json' ) );
	}

	/**
	 * Get base WordPress theme Path
	 */
	public static function eval_get_theme_root() {
		return \WP_CLI::runcommand( 'eval "echo get_theme_root();"', array( 'return' => 'stdout' ) );
	}

	/**
	 * Get List Of WordPress Themes
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_get_themes/
	 * @when after_wp_load
	 */
	public static function get_list_themes() {

		//Get List Of themes
		$themes = wp_get_themes();

		//Creat Empty List
		$themes_list = array();

		//Get Current stylesheet theme
		$current_theme = self::eval_get_current_theme();

		//List Of Data
		$data = array( 'name', 'title', 'version', 'parent_theme', 'template_dir', 'stylesheet_dir', 'template', 'stylesheet', 'screenshot', 'description', 'author', 'tags', 'theme_root', 'theme_root_uri' );

		//Push To list
		foreach ( $themes as $stylesheet => $theme_val ) {

			//Get Theme Detail
			$theme = wp_get_theme( $stylesheet );

			//Added To list
			$info = array(
				'slug'     => $stylesheet, # twentyseventeen
				'path'     => $theme->__get( 'template_dir' ), # Complete path folder theme
				'activate' => ( $current_theme == strtolower( $stylesheet ) ? true : false ),
			);
			foreach ( $data as $key ) {
				$info[ $key ] = $theme->__get( $key );
			}

			//Push
			$themes_list[] = PHP::array_change_key_case_recursive( $info );
		}

		return $themes_list;
	}

	/**
	 * Search in WordPress Theme
	 *
	 * @param array $args
	 * @return array|bool
	 */
	public static function search_wordpress_themes( $args = array() ) {

		$defaults = array(
			/**
			 * Search By :
			 * name   -> Theme name
			 * folder -> Folder of theme
			 */
			'search_by' => 'name',
			/**
			 * Search Value
			 */
			'search'    => '',
			/**
			 * Return First item
			 */
			'first'     => false
		);

		// Parse incoming $args into an array and merge it with $defaults
		$args = PHP::parse_args( $args, $defaults );

		//Get List Of Plugins
		$list = self::get_list_themes();

		//Get List Search Result
		$search_result = array();

		//Start Loop theme List
		foreach ( $list as $theme ) {

			//is in Search
			$is_in_search = false;

			//Check Type Search
			switch ( $args['search_by'] ) {
				case "name":
					if ( strtolower( $theme['name'] ) == strtolower( $args['search'] ) ) {
						$is_in_search = true;
					}
					break;

				case "folder":
					if ( stristr( $theme['path'], strtolower( $args['search'] ) ) ) {
						$is_in_search = true;
					}
					break;
			}

			//Add To list Function
			if ( $is_in_search === true ) {
				$search_result[] = $theme;
			}
		}

		//Return
		if ( empty( $search_result ) ) {
			return false;
		} else {
			//Get only first result
			if ( isset( $args['first'] ) ) {
				return array_shift( $search_result );
			} else {
				//Get All result
				return $search_result;
			}
		}
	}

	/**
	 * Update Themes List
	 *
	 * @param $pkg_themes
	 * @param array $current_themes_list
	 * @param array $options
	 */
	public static function update_themes( $pkg_themes, $current_themes_list = array(), $options = array() ) {

		//Load WP_THEMES_API
		$themes_api = new WP_Themes_Api();

		//Get theme root
		$theme_root = self::eval_get_theme_root();

		//Default Params
		$defaults = array(
			'force'  => false,
			'log'    => true,
			'remove' => true
		);
		$args     = PHP::parse_args( $options, $defaults );

		//Check Removed Theme
		if ( isset( $args['remove'] ) and ! empty( $current_themes_list ) ) {

			foreach ( $current_themes_list as $wp_theme_stylesheet => $wp_theme_ver ) {

				//if not exist in Package themes list then be Removed
				$exist = false;
				foreach ( $pkg_themes as $stylesheet => $version ) {
					$exist = ( $wp_theme_stylesheet == $stylesheet ? true : false );
				}

				if ( $exist === false ) {

					//Check if is active theme
					if ( self::eval_get_current_theme() == $wp_theme_stylesheet ) {

						if ( isset( $args['log'] ) and $args['log'] === true ) {
							CLI::pl_wait_end();
							install::add_detail_log( CLI::_e( 'package', 'er_delete_no_theme', array( "[theme]" => $wp_theme_stylesheet ) ) );
							CLI::pl_wait_start();
						}
					} else {
						//Removed From Current theme
						unset( $current_themes_list[ $wp_theme_stylesheet ] );

						//Run Removed Theme
						$cmd = "theme delete {$wp_theme_stylesheet}";
						CLI::run_command( $cmd, array( 'exit_error' => false ) );

						//Add Log
						if ( isset( $args['log'] ) and $args['log'] === true ) {
							CLI::pl_wait_end();
							install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Removed", "[slug]" => $wp_theme_stylesheet, "[type]" => "theme", "[more]" => "" ) ) );
							CLI::pl_wait_start();
						}
					}
				}

			}
		}

		//Check install or Update Theme
		foreach ( $pkg_themes as $stylesheet => $version ) {

			//Check Exist Plugin
			$wp_exist = false;
			foreach ( $current_themes_list as $wp_theme_stylesheet => $wp_theme_ver ) {
				$wp_exist = ( $wp_theme_stylesheet == $stylesheet ? true : false );
			}

			//Check theme from Url or WordPress
			$from_url = ( PHP::is_url( $version ) === false ? false : true );

			# install theme
			if ( $wp_exist === false ) {

				//Check From URL or WordPress theme
				if ( $from_url === true ) {
					# Theme From WordPress
					$prompt = $version;
				} else {
					# Theme from Source
					$prompt = $stylesheet;
					if ( $version != "latest" ) {
						$prompt .= ' --version=' . $version;
					}
				}

				//Run Command
				$cmd = "theme install {$prompt} --force";
				CLI::run_command( $cmd, array( 'exit_error' => false ) );

				//Add Log
				if ( isset( $args['log'] ) and $args['log'] === true ) {
					CLI::pl_wait_end();
					install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Added", "[slug]" => $stylesheet . ( ( $version != "latest" and PHP::is_semver_version( $version ) === true ) ? ' ' . CLI::color( "v" . $version, "P" ) : '' ), "[type]" => "theme", "[more]" => "" ) ) );
					CLI::pl_wait_start();
				}

				//Sanitize Folder Theme
				if ( $from_url === true and ! empty( $theme_root ) ) {
					//Get Last Dir
					$last_dir = FileSystem::sort_dir_by_date( $theme_root, "DESC" );

					//Sanitize
					PHP::sanitize_github_dir( FileSystem::path_join( $theme_root, $last_dir[0] ) );
				}

				# Updated Theme
			} else {

				//Get Current Version
				$this_version = '';
				if ( $from_url === false ) {

					//Check if last version
					$this_version = ( $version == "latest" ? $themes_api->get_last_version_theme( $stylesheet ) : $version );
				}

				//Check Exist in WordPress Plugin List
				$update = true;
				if ( ! empty( $this_version ) ) {
					# Use WordPress theme
					$update = ( $this_version == $current_themes_list[ $stylesheet ] ? false : true );
				}

				//Update
				if ( ( isset( $args['force'] ) and $args['force'] === true ) || $update === true ) {

					//Check From URL or WordPress Plugin
					$prompt = $stylesheet;
					if ( $from_url == false ) {
						$prompt .= ' --version=' . $this_version;
					}

					//Run Command
					$cmd = "theme update {$prompt} --force";
					CLI::run_command( $cmd, array( 'exit_error' => false ) );

					//Add Log
					if ( isset( $args['log'] ) and $args['log'] === true ) {
						CLI::pl_wait_end();
						install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Updated", "[slug]" => $stylesheet . ( ( $version != "latest" and PHP::is_semver_version( $version ) === true ) ? ' ' . CLI::color( "v" . $version, "P" ) : '' ), "[type]" => "theme" ) ) );
						CLI::pl_wait_start();
					}
				}
			}
		}

		if ( isset( $args['log'] ) ) {
			CLI::pl_wait_end();
		}
	}

	/**
	 * Switch theme in WordPress
	 *
	 * @param $stylesheet
	 * @return array
	 */
	public static function switch_theme( $stylesheet ) {

		//Get List exist theme
		$exist_list = self::eval_themes_list();

		//Get Active theme
		$active_theme = self::eval_get_current_theme();

		//Check is active theme
		if ( $stylesheet == $active_theme ) {
			return array( 'status' => true, 'data' => CLI::_e( 'package', 'is_now_theme_active', array( "[stylesheet]" => $stylesheet ) ) );
		}

		//Check exist theme stylesheet
		if ( ! in_array( $stylesheet, $exist_list ) ) {
			return array( 'status' => false, 'data' => CLI::_e( 'package', 'theme_not_found', array( "[stylesheet]" => $stylesheet ) ) );
		} else {
			//run switch theme
			CLI::run_command( "theme activate {$stylesheet}", array( 'exit_error' => false ) );

			//log
			return array( 'status' => true, 'data' => CLI::_e( 'package', 'switch_to_theme', array( "[stylesheet]" => $stylesheet ) ) );
		}
	}


}