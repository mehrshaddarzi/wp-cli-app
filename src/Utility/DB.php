<?php

namespace WP_CLI_APP\Utility;


use WP_CLI_APP\Package\Arguments\Config;

class DB {

	/**
	 * Check Wordpress Already exist
	 *
	 * @package wp-cli-application
	 * @version 1.0.0
	 * @param array $args
	 */
	public static function create_wp_db( $args = array() ) {

		/**
		 * Check connect to MySql
		 *
		 */
		@$conn = mysqli_connect( $args['dbhost'], $args['dbuser'], $args['dbpass'] );
		if ( ! $conn ) {
			WP_CLI::error( "Connection failed To " . $args['dbhost'] . " Mysql Server" );

			return;
		}

		/**
		 * Rebase MySql Database
		 *
		 */
		$sql = "CREATE DATABASE {$args['dbname']} CHARACTER SET utf8 COLLATE utf8_general_ci;";
		mysqli_query( $conn, $sql );


		/**
		 * Reset All Table
		 *
		 */
		mysqli_query( $conn, 'SET foreign_key_checks = 0' );
		mysqli_select_db( $conn, $args['dbname'] );
		if ( $result = mysqli_query( $conn, 'SHOW TABLES' ) ) {
			while ( $table = mysqli_fetch_array( $result, MYSQLI_NUM ) ) {
				mysqli_query( $conn, 'DROP TABLE IF EXISTS ' . $table[0] );
			}
		}
		mysqli_query( $conn, 'SET foreign_key_checks = 1' );

	}


	/**
	 * Remove Wordpress Database
	 *
	 * @package wp-cli-application
	 * @version 1.0.0
	 * @param array $args
	 */
	public static function drop_wp_db( $args = array() ) {

		/**
		 * Check Connect to MySql
		 */
		@$conn = mysqli_connect( $args['dbhost'], $args['dbuser'], $args['dbpass'] );
		if ( ! $conn ) {
			WP_CLI::error( "Connection failed To " . $args['dbhost'] . " Mysql Server" );

			return;
		}

		/**
		 * Drop MySql Database
		 */
		$sql = "DROP DATABASE {$args['dbname']};";
		mysqli_query( $conn, $sql );
	}


	/**
	 * Change Wordpress database Prefix
	 *
	 * @param $new_prefix
	 * @return bool
	 */
	public static function change_prefix_db( $new_prefix ) {
		global $wpdb;

		//Set Number Step
		$step = 4;

		//Step 1 : Change $table_prefix in wp-config
		$config     = Config::get_config_transformer();
		$old_prefix = $wpdb->prefix;
		$config->update( 'variable', 'table_prefix', $new_prefix );
		CLI::log( CLI::color( "Step 1/" . $step . ":", "b" ) . " Change table_prefix variable in wp-config.php" );

		//Step 2 : Rename Wordpress Table
		$show_table_query = sprintf( 'SHOW TABLES LIKE "%s%%";', $wpdb->esc_like( $old_prefix ) );
		$tables           = $wpdb->get_results( $show_table_query, ARRAY_N );
		if ( ! $tables ) {
			CLI::error( 'MySQL error: ' . $wpdb->last_error );
			return false;
		}
		foreach ( $tables as $table ) {
			$table        = substr( $table[0], strlen( $old_prefix ) );
			$rename_query = sprintf( "RENAME TABLE `%s` TO `%s`;", $old_prefix . $table, $new_prefix . $table );
			if ( false === $wpdb->query( $rename_query ) ) {
				CLI::error( 'MySQL error: ' . $wpdb->last_error );
				return false;
			}
		}
		CLI::log( CLI::color( "Step 2/" . $step . ":", "b" ) . " Rename All Wordpress Table Name in Database" );

		//Step 3 : Update Blog Options Table
		$update_query = $wpdb->prepare( "UPDATE `{$new_prefix}options` SET option_name = %s WHERE option_name = %s LIMIT 1;",
			$new_prefix . 'user_roles',
			$old_prefix . 'user_roles'
		);
		if ( ! $wpdb->query( $update_query ) ) {
			CLI::error( 'MySQL error: ' . $wpdb->last_error );
			return false;
		}
		//Check For MultiSite
		if ( function_exists( 'is_multisite' ) ) {
			if ( is_multisite() ) {
				$sites = get_sites( array( 'number' => false ) );
				if ( $sites ) {
					foreach ( $sites as $site ) {
						$update_query = $wpdb->prepare( "UPDATE `{$new_prefix}{$site->blog_id}_options` SET option_name = %s WHERE option_name = %s LIMIT 1;",
							$new_prefix . $site->blog_id . '_user_roles',
							$old_prefix . $site->blog_id . '_user_roles'
						);
						if ( ! $wpdb->query( $update_query ) ) {
							CLI::error( 'MySQL error: ' . $wpdb->last_error );
							return false;
						}
					}
				}
			}
		}
		CLI::log( CLI::color( "Step 3/" . $step . ":", "b" ) . " Update Blog Options Table" );

		//Step 4 : Update User Meta Prefix
		$rows = $wpdb->get_results( "SELECT `meta_key` FROM `{$new_prefix}usermeta`;" );
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$meta_key_prefix = substr( $row->meta_key, 0, strlen( $old_prefix ) );
				if ( $meta_key_prefix !== $old_prefix ) {
					continue;
				}
				$new_key      = $new_prefix . substr( $row->meta_key, strlen( $old_prefix ) );
				$update_query = $wpdb->prepare( "UPDATE `{$new_prefix}usermeta` SET meta_key=%s WHERE meta_key=%s LIMIT 1;",
					$new_key,
					$row->meta_key
				);
				if ( ! $wpdb->query( $update_query ) ) {
					CLI::error( 'MySQL error: ' . $wpdb->last_error );
					return false;
				}
			}
		}
		CLI::log( CLI::color( "Step 4/" . $step . ":", "b" ) . " Update User Meta Prefix" );

		return true;
	}

}