<?php

namespace WP_CLI_APP\Package\Arguments;

/**
 * Get WordPress Package Constant List
 * @package WP_CLI_APP\Package\Arguments
 */
class Constant extends \WPConfigTransformer {
	//__construct
	public function __construct( $wp_config_path ) {
		parent::__construct( $wp_config_path );
	}

	//Get List Constant
	public function parse_wp_config( $src ) {
		return parent::parse_wp_config( $src );
	}
}