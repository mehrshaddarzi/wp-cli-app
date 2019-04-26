<?php

namespace WP_CLI_APP\Utility;

use WP_CLI_APP\Package\Arguments\Dir;

/**
 * Class Plugin
 * @package WP_CLI_APP\Helper
 *
 */
class Plugin {
	/**
	 * Get Plugins Directory
	 */
	public $plugins_path;

	/**
	 * List Of Template Plugin
	 */
	public $template;

	/**
	 * Get Templates Path
	 */
	public $templates_path;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		//Get Plugins Path
		$this->plugins_path = Dir::get_plugins_dir();

		//Get Templates List Path
		$this->templates_path = FileSystem::path_join( WP_CLI_APP_PATH, "src/Plugin" );

		//Get List Of Template
		$this->template = $this->get_list_template();
	}

	/**
	 * Get List Of Plugin Template
	 *
	 * @param string $template | get Custom Template information
	 * @return array|string
	 */
	public function get_list_template( $template = '' ) {

		//Create Empty array
		$list = array();

		//Get List Templates
		$templates = FileSystem::get_dir_contents( $this->templates_path );
		foreach ( $templates as $t ) {
			if ( pathinfo( $t, PATHINFO_EXTENSION ) == "php" ) {
				$class_name          = str_replace( ".php", "", basename( $t ) );
				$obj                 = $this->init_template( $class_name );
				$list[ $class_name ] = array();
				foreach ( array( 'name', 'type', 'link', 'replace', 'rename', 'command', 'description', 'author_url', 'version' ) as $opt ) {
					$list[ $class_name ][ $opt ] = $obj->{$opt};
				}
			}
		}

		if ( ! empty( $template ) and array_key_exists( $template, $list ) ) {
			return $list[ $template ];
		} else {
			return $list;
		}
	}

	/**
	 * Search Template
	 *
	 * @param $template | slug
	 * @return bool|mixed
	 */
	public function search_template( $template ) {
		$list = $this->get_list_template();
		if ( array_key_exists( $template, $list ) ) {
			return $list[ $template ];
		}

		return false;
	}

	/**
	 * init Template Class
	 *
	 * @param $template
	 * @return mixed
	 */
	public function init_template( $template ) {
		$class = "WP_CLI_APP\Plugin\\" . $template;
		$obj   = new $class;
		return $obj;
	}

	/**
	 * Create a new Plugin
	 *
	 * @param string $template
	 * @param array $args
	 * @return bool
	 */
	public function create( $template = '', $args = array() ) {
		global $WP_CLI_APP;

		//Get Package information
		$step         = 3;
		$template_obj = $this->init_template( $template );

		//if Set workspace after Setup
		$set_workspace = CLI::get_flag_value( $args, 'set_workspace', 0 );
		if ( $set_workspace == 1 ) {
			$step ++;
		}

		//if Active plugin after Setup
		$active_plugin = CLI::get_flag_value( $args, 'active_plugin', 0 );
		if ( $active_plugin == 1 ) {
			$step ++;
		}

		//Prepare Plugin information
		$plugin_slug        = self::sanitize_plugin_slug( CLI::get_flag_value( $args, 'plugin_slug', $WP_CLI_APP['default_plugin_slug'] ) );
		$namespace          = self::sanitize_namespace( CLI::get_flag_value( $args, 'namespace', '' ) );
		$init_plugin_data   = array(
			'plugin_name'        => sanitize_text_field( CLI::get_flag_value( $args, 'plugin_name', $WP_CLI_APP['default_plugin_name'] ) ),
			'plugin_url'         => esc_url_raw( CLI::get_flag_value( $args, 'plugin_url', $WP_CLI_APP['default_plugin_url'] ) ),
			'plugin_slug'        => $plugin_slug,
			'plugin_slug_class'  => self::sanitize_slug_uc_first( $plugin_slug ),
			'plugin_description' => sanitize_text_field( CLI::get_flag_value( $args, 'plugin_description', $WP_CLI_APP['default_plugin_description'] ) ),
			'plugin_author_name' => sanitize_text_field( CLI::get_flag_value( $args, 'author_name', $WP_CLI_APP['default_author_name'] ) ),
			'plugin_author_url'  => esc_url_raw( CLI::get_flag_value( $args, 'author_url', $WP_CLI_APP['default_author_url'] ) ),
			'plugin_text_domain' => preg_replace( '/[^a-zA-Z0-9-_.]/', '', CLI::get_flag_value( $args, 'text_domain', $WP_CLI_APP['default_text_domain'] ) ),
			'plugin_namespace'   => $namespace,
		);

		//Check Type Of Plugin for Download
		$get_template_data = $this->get_list_template( $template );
		$type              = strtolower( $get_template_data['type'] );
		switch ( $type ) {
			case "git":

				//Show Step
				CLI::br();
				CLI::log( CLI::color( "Step 1/" . $step . ":", "b" ) . " Clone Plugins Files." );

				//Check Git Command install in your Server
				Git::is_exist_git();

				//Run Clone
				chdir( $this->plugins_path );
				CLI::exec( "git clone " . $get_template_data['link'] );
				sleep( 3 );

				//Get Last Directory
				$last_dir             = FileSystem::sort_dir_by_date( $this->plugins_path, "DESC" );
				$last_dir             = $last_dir[0];
				$full_path_plugin_dir = FileSystem::path_join( $this->plugins_path, $last_dir );

				//Remove Git folder
				CLI::exec( "rm -f -r " . FileSystem::path_join( $full_path_plugin_dir, ".git" ) );

				break;
			case "url":

				//Show Step
				CLI::br();
				CLI::log( CLI::color( "Step 1/" . $step . ":", "b" ) . " Start Downloading Plugin Package." );

				//Download Zip Plugin Package
				$plugin_zip = FileSystem::download_file( $get_template_data['link'], $this->plugins_path );
				if ( $plugin_zip === false ) {
					CLI::error( "Package was not downloaded. Please try again." );
					exit;
				}

				//Unzip Package
				$unzip = FileSystem::unzip( $plugin_zip );
				if ( $unzip === false ) {
					@unlink( $plugin_zip );
					CLI::error( "Error in unzip package file. Please tray again" );
					exit;
				}

				//Remove Zip File
				sleep( 1 );
				@unlink( $plugin_zip );

				//Get Last Directory
				sleep( 1 );
				$last_dir             = FileSystem::sort_dir_by_date( $this->plugins_path, "DESC" );
				$last_dir             = $last_dir[0];
				$full_path_plugin_dir = FileSystem::path_join( $this->plugins_path, $last_dir );

				break;
			default:
				CLI::error( "You Template `type` is not valid" );
				exit;
		}

		//Run After Copy Plugin action
		if ( PHP::search_method_from_class( $template_obj, 'after_copy' ) and isset( $last_dir ) ) {
			$plugin_dir           = $template_obj->after_copy( $last_dir, $init_plugin_data );
			$full_path_plugin_dir = FileSystem::path_join( $this->plugins_path, $plugin_dir );
		} else {
			$plugin_dir = $last_dir;
		}

		//init plugin data
		CLI::log( CLI::color( "Step 2/" . $step . ":", "b" ) . " push plugin data to package." );
		$plugin_data = self::init_plugin_data( $init_plugin_data );

		//Rename Files
		if ( count( $get_template_data['rename'] ) > 0 ) {
			foreach ( $get_template_data['rename'] as $r_key => $r_val ) {
				foreach ( array( 'r_key', 'r_val' ) as $v ) {
					${$v} = str_ireplace( array_keys( $plugin_data ), array_values( $plugin_data ), ${$v} );
				}
				$file = FileSystem::normalize_path( FileSystem::path_join( $full_path_plugin_dir, $r_key ) );
				if ( realpath( $file ) != false ) {
					@rename( $file, FileSystem::normalize_path( FileSystem::path_join( $full_path_plugin_dir, $r_val ) ) );
				}
			}
		}

		//Replace in Files
		sleep( 3 );
		$all_file_list  = FileSystem::get_dir_contents( $full_path_plugin_dir, true );
		$search_string  = array_merge( array_keys( $plugin_data ), array_keys( $get_template_data['replace'] ) );
		$replace_string = array_merge( array_values( $plugin_data ), array_values( $get_template_data['replace'] ) );
		foreach ( $all_file_list as $f ) {
			FileSystem::search_replace_file( $f, $search_string, $replace_string, true );
		}

		//add config file
		CLI::log( CLI::color( "Step 3/" . $step . ":", "b" ) . " Add Config File `cli.json` to plugin folder." );
		$config = array(
			'type'     => 'plugin',
			'template' => $template,
			'slug'     => $plugin_slug
		);
		if ( ! empty( $namespace ) ) {
			$config['namespace'] = $namespace;
		}
		$config_file = WorkSpace::create_config_file( $full_path_plugin_dir, $config );
		if ( $config_file === false ) {
			FileSystem::remove_dir( $full_path_plugin_dir, true );
			CLI::error( "WorkSpace config file was not Created. please try again." );
			exit;
		}

		//Run Custom Command in this Template
		if ( count( $get_template_data['command'] ) > 0 ) {
			chdir( $full_path_plugin_dir );
			foreach ( $get_template_data['command'] as $c ) {
				CLI::exec( $c );
				sleep( 2 );
			}
		}

		//Run after install Complete
		if ( PHP::search_method_from_class( $template_obj, 'after_setup' ) and isset( $plugin_dir ) ) {
			$template_obj->after_setup( $plugin_dir, $init_plugin_data );
		}

		//Set workSpace
		if ( $set_workspace == 1 ) {
			$plugin_check = WorkSpace::search_plugins_or_themes( $plugin_dir, "plugin" );
			if ( $plugin_check != false ) {
				update_option( $WP_CLI_APP['workspace'], array(
					"type"  => $plugin_check['type'],
					"value" => $plugin_dir,
				) );
				CLI::log( CLI::color( "Step 4/" . $step . ":", "b" ) . " The WorkSpace Was Set." );
			}
		}

		//Active Plugin
		if ( $active_plugin == 1 ) {
			$plugin_file = FileSystem::path_join( $full_path_plugin_dir, $plugin_slug . '.php' );
			if ( file_exists( $plugin_file ) ) {
				$active_plugin = self::activate_plugin( $plugin_file );
				if ( $active_plugin === true ) {
					CLI::log( CLI::color( "Step 5/" . $step . ":", "b" ) . " The Plugin activated" );
				}
			}
		}

		return true;
	}

	/**
	 * Sanitize plugin Slug name
	 *
	 * @param $plugin_slug
	 * @return string
	 */
	public static function sanitize_plugin_slug( $plugin_slug ) {
		return FileSystem::sanitize_folder_name( $plugin_slug );
	}

	/**
	 * Sanitize Plugin slug Uc first Character
	 *
	 * @param $slug
	 * @return mixed|null|string|string[]
	 */
	public static function sanitize_slug_uc_first( $slug ) {
		$slug = self::sanitize_plugin_slug( $slug );
		$slug = str_replace( "-", "_", $slug );
		$slug = implode( '_', array_map( 'ucfirst', explode( '_', $slug ) ) );
		return $slug;
	}

	/**
	 * init Plugin data
	 *
	 * @param array $args
	 * @return array
	 */
	public static function init_plugin_data( $args = array() ) {
		return array(
			'{{plugin_name}}'        => $args['plugin_name'],
			'{{plugin_url}}'         => $args['plugin_url'],
			'{{plugin_slug}}'        => $args['plugin_slug'],
			'{{plugin_slug_class}}'  => $args['plugin_slug_class'],
			'{{plugin_description}}' => $args['plugin_description'],
			'{{plugin_author_name}}' => $args['plugin_author_name'],
			'{{plugin_author_url}}'  => $args['plugin_author_url'],
			'{{plugin_text_domain}}' => $args['plugin_text_domain'],
			'{{plugin_namespace}}'   => $args['plugin_namespace'],
		);
	}

	/**
	 * Sanitize NameSpace
	 *
	 * @param $namespace
	 * @return mixed
	 */
	public static function sanitize_namespace( $namespace ) {
		$namespace = sanitize_text_field( $namespace );
		$namespace = preg_replace( '/\s+/', '', $namespace );
		$namespace = str_replace( "/", '\\\\', $namespace );
		$namespace = str_replace( "\\", "\\\\", $namespace );

		return $namespace;
	}

	/**
	 * Activate Plugin
	 *
	 * @param $plugin_file | 'plugin-dir/plugin-file.php'
	 * @return bool
	 */
	public static function activate_plugin( $plugin_file ) {
		if ( ! function_exists( 'activate_plugin' ) ) {
			include_once( Wordpress::get_base_path() . 'wp-admin/includes/plugin.php' );
		}
		$result = activate_plugin( $plugin_file );
		if ( is_wp_error( $result ) ) {
			return false;
		} else {
			return true;
		}
	}


}