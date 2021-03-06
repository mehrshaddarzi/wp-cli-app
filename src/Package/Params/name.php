<?php

namespace WP_CLI_APP\Package\Params;

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\WP_CLI_ERROR;

class name {

	/**
	 * Get Wordpress Package options
	 *
	 * @var string
	 */
	public $package_config;

	/**
	 * Core constructor.
	 */
	public function __construct() {
		/*
		 * Set Config Global
		 */
		$this->package_config = $GLOBALS['WP_CLI_APP']['package'];
	}

	/**
	 * Validation Package
	 *
	 * @param $pkg_array
	 * @return array
	 */
	public function validation( $pkg_array ) {

		//Create new validation
		$valid = new WP_CLI_ERROR();

		//Get name parameter
		$parameter = $pkg_array['name'];

		//Check Validation parameter
		$check = $this->sanitize_name( $parameter, true );
		if ( $check['status'] === false ) {
			foreach ( $check['data'] as $error ) {
				$valid->add_error( $error );
			}
		} else {
			$valid->add_success( array_shift( $check['data'] ) );
		}

		return $valid->result();
	}

	/**
	 * Sanitize Package name parameter
	 *
	 * @param $var
	 * @param bool $validate
	 * @return string|boolean|array
	 * @since 1.0.0
	 */
	public function sanitize_name( $var, $validate = false ) {

		//Create new validation
		$valid = new WP_CLI_ERROR();

		//Check is Empty
		if ( empty( $var ) ) {
			$valid->add_error( CLI::_e( 'package', 'empty_val', array( "[key]" => "name" ) ) );

			//Check is array
		} elseif ( is_array( $var ) ) {
			$valid->add_error( CLI::_e( 'package', 'is_not_string', array( "[key]" => "Package name" ) ) );

		} else {

			//Check @ in Package Name
			if ( stristr( $var, $this->package_config['separator_name'] ) === false ) {
				$valid->add_error( CLI::_e( 'package', 'er_package_name' ) );
			} else {

				//separate username and Package name
				$pkg = explode( $this->package_config['separator_name'], $var );
				if ( count( $pkg ) != 2 || empty( $pkg[0] ) || empty( $pkg[1] ) || is_numeric( substr( $pkg[0], 0, 1 ) ) || is_numeric( substr( $pkg[1], 0, 1 ) ) ) {
					$valid->add_error( CLI::_e( 'package', 'er_package_name' ) );
				} else {

					//Save original Package name
					$pkg_raw = $pkg;

					//Sanitize Data
					$pkg = array_map( function ( $value ) {
						//Trim Word
						$return = PHP::to_lower_string( $value );
						//Convert _ to -
						$return = str_ireplace( "_", "-", $return );
						//Preg Data
						$return = preg_replace( $this->package_config['preg_username'], '', $return );
						return $return;
					}, $pkg );

					//Check different between user input and sanitize
					for ( $i = 0; $i < count( $pkg_raw ); $i ++ ) {
						if ( PHP::to_lower_string( $pkg_raw[ $i ] ) != $pkg[ $i ] ) {
							$valid->add_error( CLI::_e( 'package', 'er_package_name' ) );
							break;
						}
					}

					//Return Sanitize Data
					$var = implode( $this->package_config['separator_name'], $pkg );
					$valid->add_success( $var );
				}
			}
		}

		return ( $validate === true ? $valid->result() : $var );
	}

}