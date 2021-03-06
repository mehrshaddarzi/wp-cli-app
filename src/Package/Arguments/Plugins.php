<?php

namespace WP_CLI_APP\Package\Arguments;

use Faker\Provider\File;
use WP_CLI_APP\API\WP_Plugins_Api;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\Wordpress;
use WP_CLI_APP\Package\Utility\install;

class Plugins {
	/**
	 * Get WordPress Plugins path
	 *
	 * @return mixed
	 */
	public static function eval_get_plugins_path() {
		return \WP_CLI::runcommand( 'eval "if(defined(\'WP_PLUGIN_DIR\')) { echo WP_PLUGIN_DIR; } else { echo \'\'; }"', array( 'return' => 'stdout' ) );
	}

	/**
	 * Get List Of WordPress Plugin (not contain mu-plugins)
	 * @see https://developer.wordpress.org/reference/functions/get_plugins/
	 */
	public static function get_list_plugins() {

		//Check Function Exist
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( Wordpress::get_base_path() . 'wp-admin/includes/plugin.php' );
		}

		//Get List Of Plugin
		$plugins = get_plugins();

		//Creat Empty List
		$plugin_list = array();

		//Push To list
		foreach ( $plugins as $plugin_slug => $plugin_val ) {

			//Get Plugin folder name
			$exp    = explode( "/", $plugin_slug );
			$folder = ( stristr( $exp[0], ".php" ) != false ? "" : $exp[0] );

			//Ful path Plugin
			$path = FileSystem::path_join( Dir::get_plugins_dir(), $plugin_slug );

			//Added To list
			$basic_inf = array(
				'slug'        => $plugin_slug, # hello-dolly/hello.php
				'folder'      => $folder, #hello-dolly
				'path'        => $path, # Complete Path .php Plugin file
				'path_folder' => dirname( $path ), # complete Path without php file
				'activate'    => ( is_plugin_active( $plugin_slug ) ? true : false )
			);

			//Push Plugins key
			$plugin_list[] = PHP::array_change_key_case_recursive( array_merge( $basic_inf, $plugin_val ) );
		}

		return $plugin_list;
	}

	/**
	 * Search in WordPress Plugin
	 *
	 * @param array $args
	 * @return array|bool
	 */
	public static function search_wordpress_plugins( $args = array() ) {

		$defaults = array(
			/**
			 * Search By :
			 * name   -> Plugin name
			 * folder -> Folder of plugin
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
		$list = self::get_list_plugins();

		//Get List Search Result
		$search_result = array();

		//Start Loop Plugins List
		foreach ( $list as $plugin ) {

			//is in Search
			$is_in_search = false;

			//Check Type Search
			switch ( $args['search_by'] ) {
				case "name":
					if ( strtolower( $plugin['name'] ) == strtolower( $args['search'] ) ) {
						$is_in_search = true;
					}
					break;

				case "folder":
					if ( stristr( $plugin['path'], strtolower( $args['search'] ) ) ) {
						$is_in_search = true;
					}
					break;
			}

			//Add To list Function
			if ( $is_in_search === true ) {
				$search_result[] = $plugin;
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
	 * Update Plugin List
	 *
	 * @param $pkg_plugins
	 * @param array $current_plugin_list
	 * @param array $options
	 */
	public static function update_plugins( $pkg_plugins, $current_plugin_list = array(), $options = array() ) {

		//Load WP_PLUGINS_API
		$plugins_api = new WP_Plugins_Api;

		//Get plugins path
		$plugins_path = FileSystem::normalize_path( self::eval_get_plugins_path() );

		//Default Params
		$defaults = array(
			'force'  => false,
			'log'    => true,
			'remove' => true
		);
		$args     = PHP::parse_args( $options, $defaults );

		//Check Removed Plugins
		if ( isset( $args['remove'] ) and ! empty( $current_plugin_list ) ) {

			$p = 0;
			foreach ( $current_plugin_list as $wp_plugin ) {

				//if not exist in Package Plugin list then be Removed
				$exist = false;
				foreach ( $pkg_plugins as $plugin ) {
					if ( $wp_plugin['folder'] == $plugin['slug'] ) {
						$exist = true;
					} else {
						//Removed From Current Plugin
						unset( $current_plugin_list[ $p ] );
					}
				}

				if ( $exist === false ) {

					//Run Removed Plugin
					$cmd = "plugin uninstall {$wp_plugin['folder']} --deactivate";
					CLI::run_command( $cmd, array( 'exit_error' => false ) );

					//Add Log
					if ( isset( $args['log'] ) and $args['log'] === true ) {
						CLI::pl_wait_end();
						install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Removed", "[slug]" => $wp_plugin['folder'], "[type]" => "plugin", "[more]" => "" ) ) );
						CLI::pl_wait_start();
					}
				}

				$p ++;
			}
		}

		//De'active or Active Plugin
		if ( ! empty( $current_plugin_list ) ) {

			foreach ( $pkg_plugins as $plugin ) {

				//Status of this Plugin [deactivate or activate]
				$pkg_status = $plugin['activate'];

				//WordPress Plugin status
				$wp_status = null;
				foreach ( $current_plugin_list as $wp_plugin ) {
					if ( $wp_plugin['folder'] == $plugin['slug'] ) {
						$wp_status = $wp_plugin['activate'];
					}
				}

				//Check different
				if ( ! is_null( $wp_status ) and isset( $wp_plugin['folder'] ) and $pkg_status != $wp_status ) {

					//Run Command plugin
					$cmd = "plugin " . ( $pkg_status === true ? 'activate' : 'deactivate' ) . " {$plugin['slug']}";
					CLI::run_command( $cmd, array( 'exit_error' => false ) );

					//Add Log
					if ( isset( $args['log'] ) and $args['log'] === true ) {
						CLI::pl_wait_end();
						install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => ( $pkg_status === true ? 'Activate' : 'Deactivate' ), "[slug]" => $plugin['slug'], "[type]" => "plugin", "[more]" => "" ) ) );
						CLI::pl_wait_start();
					}
				}

			}
		}

		//Check install or Update Plugin
		foreach ( $pkg_plugins as $plugin ) {

			//Check Exist Plugin
			$wp_exist = false;
			$c        = 0;
			foreach ( $current_plugin_list as $wp_plugin ) {
				if ( $wp_plugin['folder'] == $plugin['slug'] ) {
					$key_list = $c;
					$wp_exist = true;
				}

				$c ++;
			}

			# install plugin
			if ( $wp_exist === false ) {

				//Check From URL or WordPress Plugin
				if ( isset( $plugin['url'] ) ) {
					$prompt = $plugin['url'];
				} else {
					$prompt = $plugin['slug'];
					if ( $plugin['version'] != "latest" ) {
						$prompt .= ' --version=' . $plugin['version'];
					}
				}

				//Check Activate
				if ( $plugin['activate'] === true ) {
					$prompt .= ' --activate';
				}

				//Run Command
				$cmd = "plugin install {$prompt} --force";
				CLI::run_command( $cmd, array( 'exit_error' => false ) );

				//Add Log
				if ( isset( $args['log'] ) and $args['log'] === true ) {
					CLI::pl_wait_end();
					install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Added", "[slug]" => $plugin['slug'] . ( ( isset( $plugin['version'] ) and PHP::is_semver_version( $plugin['version'] ) === true ) ? ' ' . CLI::color( "v" . $plugin['version'], "P" ) : '' ), "[type]" => "plugin", "[more]" => ( $plugin['activate'] === true ? CLI::color( " [activate]", "B" ) : "" ) ) ) );
					CLI::pl_wait_start();
				}

				//Sanitize Folder Plugins
				if ( isset( $plugin['url'] ) and ! empty( $plugins_path ) ) {
					//Get Last Dir
					$last_dir = FileSystem::sort_dir_by_date( $plugins_path, "DESC" );

					//Sanitize
					PHP::sanitize_github_dir( FileSystem::path_join( $plugins_path, $last_dir[0] ) );
				}

				# Updated Plugin
			} else {

				//Get Version
				$version = '';
				if ( isset( $plugin['version'] ) ) {

					//Check if last version
					$version = $plugin['version'];
					if ( $version == "latest" ) {
						$version = $plugins_api->get_last_version_plugin( $plugin['slug'] );
					}
				}

				//Check Exist in WordPress Plugin List
				$update = false;
				if ( isset( $key_list ) ) {
					if ( ! empty( $version ) ) {
						# Use WordPress Plugin
						$update = ( $version == $current_plugin_list[ $key_list ]['version'] ? false : true );
					} else {
						# Use Source
						$update = ( $plugin['url'] ? true : false );
					}
				}

				//Update
				if ( ( isset( $args['force'] ) and $args['force'] === true ) || $update === true ) {

					//Check From URL or WordPress Plugin
					$prompt = $plugin['slug'];
					if ( ! isset( $plugin['url'] ) ) {
						$prompt .= ' --version=' . $plugin['version'];
					}

					//Run Command
					$cmd = "plugin update {$prompt} --force";
					CLI::run_command( $cmd, array( 'exit_error' => false ) );

					//Add Log
					if ( isset( $args['log'] ) and $args['log'] === true ) {
						CLI::pl_wait_end();
						install::add_detail_log( CLI::_e( 'package', 'manage_item', array( "[work]" => "Updated", "[slug]" => $plugin['slug'] . ( ( isset( $plugin['version'] ) and PHP::is_semver_version( $plugin['version'] ) === true ) ? ' ' . CLI::color( "v" . $plugin['version'], "P" ) : '' ), "[type]" => "plugin" ) ) );
						CLI::pl_wait_start();
					}

				}
			}
		}

		if ( isset( $args['log'] ) ) {
			CLI::pl_wait_end();
		}
	}
}