<?php

namespace WP_CLI_APP\Package\Utility;

use WP_CLI_APP\Utility\FileSystem;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Package\Package;
use WP_CLI_APP\Utility\WP_CLI_CONFIG;

/**
 * create Wordpress Package
 * @package WP_CLI_APP\Package\Utility
 */
class create extends Package {
	/**
	 * Create new wordpress Package File
	 *
	 * @param $arg | get assoc_args from cli
	 * @return bool|array
	 */
	public function create( $arg ) {

		//Create Empty obj
		$json     = array();
		$validate = array();

		//Check default config for mysql
		$config_list = array( 'db_host', 'db_user', 'db_password', 'db_name' );
		foreach ( $config_list as $item ) {
			try {
				$get = WP_CLI_CONFIG::get( $item );
			} catch ( \Exception $e ) {
				$get = false;
			}
			if ( $get != false and ( array_key_exists( $item, $arg ) === false || ( isset( $arg[ $item ] ) and empty( trim( $arg[ $item ] ) ) ) ) ) {
				$arg[ $item ] = $get;
			}
		}

		//Check Parameter
		$args = PHP::parse_args( $arg, $this->package_config['default'] );

		//Sanitize Double Quote
		$args = array_map( function ( $value ) {
			return PHP::remove_quote( trim( $value ) );
		}, $args );

		//Get Params Data From Every Class
		foreach ( $this->package_config['params'] as $class_name ) {

			//get Class name
			$class = $this->package_config['params_namespace'] . $class_name;

			//Create new Obj from class
			$obj = new $class();

			//check init method exist in class
			if ( PHP::search_method_from_class( $obj, 'init' ) ) {

				//Get Params
				$get_obj = $obj->init( $args );

				//if not empty array push to json file
				if ( ! empty( $get_obj ) ) {
					$json[ $class_name ] = $get_obj;
				}

				//Check list of warning
				$warning = $obj->init( $args, true );
				if ( count( $warning ) > 0 ) {
					foreach ( $warning as $text ) {
						$validate[] = $text;
					}
				}
			}
		}

		//Create Package file
		if ( FileSystem::create_json_file( $this->package_path, $json, true ) ) {
			return array( 'status' => true, 'data' => $validate );
		}

		return array( 'status' => false );
	}

}