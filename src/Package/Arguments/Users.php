<?php

namespace WP_CLI_APP\Package\Arguments;

use WP_CLI_APP\Package\Utility\install;
use WP_CLI_APP\Package\Utility\temp;
use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\PHP;
use WP_CLI_APP\Utility\WP_CLI_CONFIG;

class Users {
	/**
	 * Get List Of Default User Meta
	 */
	public static function get_default_user_meta() {
		$list = array();
		foreach ( get_wp_cli_app_config( 'package', 'default_user_meta' ) as $key ) {
			$list[] = self::sanitize_meta_name( $key );
		}
		return $list;
	}

	/**
	 * Get List Of Role in WordPress
	 *
	 * @return bool|mixed
	 */
	public static function get_list_roles() {

		//roles Option name
		$role_option = Config::get_tbl_prefix() . 'user_roles';

		//Get User roles List
		$roles = \WP_CLI::runcommand( 'eval "echo json_encode(get_option(\'' . $role_option . '\'));"', array( 'return' => 'stdout', 'parse' => 'json' ) );

		//Return
		return $roles;
	}

	/**
	 * Check Exist Role in WordPress
	 *
	 * @param string $role
	 * @return bool
	 */
	public static function check_exist_role( $role = '' ) {
		$role   = PHP::to_lower_string( $role );
		$list   = self::get_list_roles();
		$_exist = false;
		foreach ( $list as $key => $value ) {
			if ( $key == $role ) {
				$_exist = true;
				break;
			}
		}

		return $_exist;
	}

	/**
	 * Get Default clone Role
	 */
	public static function get_default_clone_role() {

		// Get Default Params
		$default_params = get_wp_cli_app_config( 'package', 'default.clone.role' );

		// Check From Global Config
		try {
			$get = WP_CLI_CONFIG::get( 'default.clone.role' );
		} catch ( \Exception $e ) {
			$get = false;
		}
		if ( $get != false ) {
			$default = $get;
		}

		// Check Exist this Role Otherwise Use Default
		if ( isset( $default ) ) {
			//Check Exist
			if ( self::check_exist_role( $default ) ) {
				return $default;
			} else {
				return $default_params;
			}
		} else {
			return $default_params;
		}
	}

	/**
	 * Clone Role in WordPress
	 *
	 * @param $role
	 * @param bool $clone_from
	 * @param bool $log
	 */
	public static function clone_role( $role, $clone_from = false, $log = false ) {
		// Sanitize Role name
		$role = PHP::remove_all_special_chars( PHP::to_lower_string( $role ) );

		// Get Default Clone
		if ( ! $clone_from ) {
			$clone_from = self::get_default_clone_role();
		}

		// Clone Role
		CLI::run_command( "role create $role \"" . ucfirst( $role ) . "\" --clone=" . $clone_from . "", array( 'exit_error' => false ) );

		//show Log
		if ( $log ) {
			install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Created", "[key]" => $role, "[type]" => "user role cloned from '" . $clone_from . "'" ) ) );
		}
	}

	/**
	 * Create WordPress User
	 *
	 * @param array $user
	 * @return bool|mixed
	 */
	public static function create_user( $user = array() ) {

		// Check Extra User role
		if ( isset( $user['role'] ) ) {
			// Check Exist role
			if ( self::check_exist_role( $user['role'] ) === false ) {
				self::clone_role( $user['role'], false, true );
			}
		}

		//Check Extra field
		$extra = '';
		foreach ( array( 'display_name', 'role', 'first_name', 'last_name' ) as $key ) {
			$extra .= ( isset( $user[ $key ] ) ? ', \'' . $key . '\' => \'' . $user[ $key ] . '\'' : '' );
		}
		$command = 'eval "$user_data = array( \'user_login\' => \'' . ( isset( $user['user_login'] ) ? $user['user_login'] : '' ) . '\', \'user_email\' => \'' . ( isset( $user['user_email'] ) ? $user['user_email'] : '' ) . '\', \'user_pass\'  => \'' . ( isset( $user['user_pass'] ) ? $user['user_pass'] : '' ) . '\'' . $extra . ' ); $user_id = wp_insert_user( $user_data ); echo $user_id;"';

		//Run Command
		$user_id = \WP_CLI::runcommand( $command, array( 'return' => 'stdout', 'parse' => 'json' ) );

		//Return Users Data
		return array( 'ID' => $user_id, 'user_login' => $user['user_login'] );
	}

	/**
	 * Sanitize Meta name
	 *
	 * @param $meta_name
	 * @return mixed
	 */
	public static function sanitize_meta_name( $meta_name ) {
		$meta_name = str_ireplace( get_wp_cli_app_config( 'package', 'tbl_prefix_key' ), Config::get_tbl_prefix(), $meta_name ); # Check Table Prefix
		return $meta_name;
	}

	/**
	 * Check Exist User Meta
	 *
	 * @param $user_id
	 * @param $meta_name
	 * @return bool
	 */
	public static function exist_user_meta( $user_id, $meta_name ) {
		$sql    = 'SELECT COUNT(*) FROM `"$wpdb->usermeta"` WHERE `user_id` = ' . $user_id . ' AND `meta_key` = \'' . self::sanitize_meta_name( $meta_name ) . '\'';
		$return = \WP_CLI::runcommand( 'eval "global $wpdb; echo $wpdb->get_var(\"' . $sql . '\");"', array( 'return' => 'stdout', 'parse' => 'json' ) );
		if ( $return > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Add/Update WordPress User Meta (Use in install Package)
	 *
	 * @param $user_id
	 * @param $meta_key
	 * @param $meta_value
	 * @return bool
	 */
	public static function update_user_meta( $user_id, $meta_key, $meta_value ) {

		//Sanitize Meta Value
		$meta_value = Options::sanitize_meta_value( $meta_value );

		// Sanitize Meta name
		$meta_key = self::sanitize_meta_name( $meta_key );

		//Check Exist Option
		$exist = self::exist_user_meta( $user_id, $meta_key );

		//We don't Use [wp user meta] Command Because we want Force Push to database
		if ( $exist === true ) {
			CLI::wpdb_query( 'UPDATE `"$wpdb->usermeta"` SET `meta_value` = \'' . $meta_value . '\' WHERE `user_id` = ' . $user_id . ' AND `meta_key` = \'' . $meta_key . '\';', array( 'exit_error' => false ) );
		} else {
			CLI::wpdb_query( 'INSERT INTO `"$wpdb->usermeta"` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, \'' . $user_id . '\', \'' . $meta_key . '\', \'' . $meta_value . '\');', array( 'exit_error' => false ) );
		}

		return $exist;
	}

	/**
	 * Add New User in WordPress Package
	 *
	 * @param array $users
	 */
	public static function add_new_user( $users = array() ) {
		# Create User
		$user = self::create_user( $users );

		# User Meta
		if ( isset( $users['meta'] ) ) {
			foreach ( $users['meta'] as $meta_key => $meta_val ) {
				self::update_user_meta( $user['ID'], $meta_key, $meta_val );
			}
		}

		# Show Log
		install::add_detail_log( CLI::_e( 'package', 'create_one_user', array( "[user_login]" => $user['user_login'], "[user_id]" => $user['ID'] ) ) );
	}

	/**
	 * Get User Data
	 *
	 * @param bool $user_id
	 * @return array
	 * @run after_wp_load
	 */
	public static function get_userdata( $user_id ) {

		# Get User Data
		$user_data = get_userdata( $user_id );
		$user_info = get_object_vars( $user_data->data );

		# Get User roles
		$user_info['role'] = $user_data->roles;

		# Get User Caps
		$user_info['cap'] = $user_data->caps;

		# Get User Meta
		$user_info['meta'] = array_map( function ( $a ) {
			return $a[0];
		}, get_user_meta( $user_id ) );

		return $user_info;
	}

	/**
	 * Update User_login
	 *
	 * @see https://codex.wordpress.org/Function_Reference/wp_update_user
	 * @param $user_login
	 * @param $user_ID
	 * @param $key
	 * @param $type
	 */
	public static function update_user_login( $user_login, $user_ID, $type, $key ) {
		global $wpdb;
		if ( username_exists( trim( $user_login ) ) ) {
			install::add_detail_log( CLI::_e( 'package', 'manage_item_error', array( "[msg]" => "that username already exists!", "[key]" => $key ) ) );
			exit;
		} else {
			$wpdb->update( $wpdb->users, array( 'user_login' => trim( $user_login ) ), array( 'ID' => $user_ID ) );
			install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Changed", "[key]" => "user_login", "[type]" => $type ) ) );
		}
	}

	/**
	 * Update User email
	 *
	 * @param $user_email
	 * @param $user_ID
	 * @param $key
	 * @param $type
	 */
	public static function update_user_email( $user_email, $user_ID, $type, $key ) {
		$_exist = email_exists( trim( $user_email ) );
		if ( $_exist and $_exist != $user_ID ) {
			install::add_detail_log( CLI::_e( 'package', 'manage_item_error', array( "[msg]" => "that email already exists!", "[key]" => $key ) ) );
			exit;
		} else {
			self::wp_update_user( $user_ID, 'user_email', $user_email, $key, $type );
		}
	}

	/**
	 * Update User in WordPress
	 *
	 * @param $user_ID
	 * @param $what | user_email
	 * @param $new_value
	 * @param $key | config: { user: { ..
	 * @param $type | admin user
	 */
	public static function wp_update_user( $user_ID, $what, $new_value, $key, $type ) {
		$edit = wp_update_user( array( 'ID' => $user_ID, $what => trim( $new_value ) ) );
		if ( is_wp_error( $edit ) ) {
			install::add_detail_log( CLI::_e( 'package', 'manage_item_error', array( "[msg]" => $edit->get_error_messages(), "[key]" => $key ) ) );
			exit;
		} else {
			install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Changed", "[key]" => $what, "[type]" => $type ) ) );
		}
	}

	/**
	 * Update WordPress Admin
	 *
	 * @param $pkg
	 * @run after_wp_load
	 */
	public static function update_users( $pkg ) {

		//Get Local Temp
		$localTemp = temp::get_temp( PHP::getcwd() );
		$tmp       = ( $localTemp === false ? array() : $localTemp );

		//Get Admin User ID
		$admin_ID = Admin::get_admin_id();

		//Disable Send email
		add_filter( 'send_email_change_email', '__return_false' );
		add_filter( 'send_password_change_email', '__return_false' );

		//Get User List
		$tmp_users = isset( $tmp['config']['users'] ) ? $tmp['config']['users'] : array();
		$pkg_users = isset( $pkg['config']['users'] ) ? $pkg['config']['users'] : array();

		// Remove Users
		if ( count( $tmp_users ) > count( $pkg_users ) ) {
			foreach ( $tmp_users as $temp_users ) {
				$_exist = false;

				// Check Exist Users in Pkg
				foreach ( $pkg_users as $pack_users ) {
					if ( PHP::to_lower_string( $pack_users['user_login'] ) == PHP::to_lower_string( $temp_users['user_login'] ) || PHP::to_lower_string( $pack_users['user_email'] ) == PHP::to_lower_string( $temp_users['user_email'] ) ) {
						$_exist = true;
					}
				}

				if ( ! $_exist ) {

					// Check User Exist in WordPress Database then Deleted
					$_exist_DB = self::check_exist_user( $temp_users );
					if ( $_exist_DB['status'] === true ) {

						# All User Post Assign To Admin Automatic
						wp_delete_user( $_exist_DB['ID'], $admin_ID );
						install::add_detail_log( CLI::_e( 'package', 'manage_item_red', array( "[work]" => "Removed", "[key]" => $_exist_DB['data']['user_login'], "[type]" => "user" ) ) );
					}
				}
			}
		}

		// Add OR Edit User item
		$x_pkg = 0;
		foreach ( $pkg_users as $pack_users ) {
			$_exist = $tmp_key = $pkg_key = false;

			// Check Exist Users in Tmp
			$x_tmp = 0;
			foreach ( $tmp_users as $temp_users ) {
				if ( PHP::to_lower_string( $pack_users['user_login'] ) == PHP::to_lower_string( $temp_users['user_login'] ) || PHP::to_lower_string( $pack_users['user_email'] ) == PHP::to_lower_string( $temp_users['user_email'] ) ) {
					$_exist  = true;
					$tmp_key = $x_tmp;
					$pkg_key = $x_pkg;
				} else {

					// Create New User if User Changed user_login and user_email together or Added New key
					$_exist_DB = self::check_exist_user( $pack_users );
					if ( $_exist_DB['status'] === false ) {
						self::add_new_user( $pack_users );
					}
				}
				$x_tmp ++;
			}

			if ( $_exist === true ) {

				// Search in WordPress DB
				$_exist_DB = self::check_exist_user( $pack_users );
				if ( $_exist_DB['status'] === true ) {
					$user_id          = $_exist_DB['ID'];
					$current_pkg_user = $pkg_users[ $pkg_key ];
					$current_tmp_user = $tmp_users[ $tmp_key ];

					// Check User_Login
					$before_user_login = isset( $current_tmp_user['user_login'] ) ? $current_tmp_user['user_login'] : $_exist_DB['data']['user_login'];
					if ( PHP::to_lower_string( $current_pkg_user['user_login'] ) != PHP::to_lower_string( $before_user_login ) ) {
						Users::update_user_login( $current_pkg_user['user_login'], $user_id, "for `" . $before_user_login . "` user", "config: { users[" . ( $x_pkg + 1 ) . "]: { user_login .." );
					}

					// Check User_email
					$before_user_email = isset( $current_tmp_user['user_email'] ) ? $current_tmp_user['user_email'] : $_exist_DB['data']['user_email'];
					if ( PHP::to_lower_string( $current_pkg_user['user_email'] ) != PHP::to_lower_string( $before_user_email ) ) {
						Users::update_user_email( $current_pkg_user['user_email'], $user_id, "for `" . $_exist_DB['data']['user_login'] . "` user", "config: { users[" . ( $x_pkg + 1 ) . "]: { user_email .." );
					}

					// Check User Pass
					if ( isset( $current_pkg_user['user_pass'] ) ) {
						$_change = false;
						if ( isset( $current_tmp_user['user_pass'] ) ) {
							if ( PHP::to_lower_string( $current_tmp_user['user_pass'] ) != PHP::to_lower_string( $current_pkg_user['user_pass'] ) ) {
								$_change = true;
							}
						} else {
							// Check in Database
							if ( wp_check_password( $current_pkg_user['user_pass'], $_exist_DB['data']['user_pass'] ) === false ) {
								$_change = true;
							}
						}

						//Update Password
						if ( $_change === true ) {
							Users::wp_update_user( $user_id, 'user_pass', $current_pkg_user['user_pass'], "config: { users[" . ( $x_pkg + 1 ) . "]: { user_pass ..", "for `" . $_exist_DB['data']['user_login'] . "` user" );
						}
					}

					// Check display_name & first_name and last_name
					foreach ( array( 'display_name', 'first_name', 'last_name' ) as $key ) {
						if ( isset( $current_pkg_user[ $key ] ) ) {
							$before_value = isset( $current_tmp_user[ $key ] ) ? $current_tmp_user[ $key ] : $_exist_DB['data'][ $key ];
							if ( PHP::to_lower_string( $current_pkg_user[ $key ] ) != PHP::to_lower_string( $before_value ) ) {
								Users::wp_update_user( $user_id, $key, $current_pkg_user[ $key ], "config: { users[" . ( $x_pkg + 1 ) . "]: { " . $key . " ..", "for `" . $_exist_DB['data']['user_login'] . "` user" );
							}
						}
					}

					// Change User role
					if ( isset( $current_pkg_user['role'] ) ) {
						$_change = false;
						if ( isset( $current_tmp_user['role'] ) ) {
							if ( PHP::to_lower_string( $current_tmp_user['role'] ) != PHP::to_lower_string( $current_pkg_user['role'] ) ) {
								$_change = true;
							}
						} else {
							// Check in WP DB
							if ( isset( $_exist_DB['data']['role'][0] ) and $_exist_DB['data']['role'][0] != $current_pkg_user['role'] ) {
								$_change = true;
							}
						}

						//Update Role
						if ( $_change === true ) {

							// Check Exist User role or Clone
							if ( self::check_exist_role( $current_pkg_user['role'] ) === false ) {
								self::clone_role( $current_pkg_user['role'], false, true );
							}
							Users::wp_update_user( $user_id, 'role', $current_pkg_user['role'], "config: { users[" . ( $x_pkg + 1 ) . "]: { role ..", "for `" . $_exist_DB['data']['user_login'] . "` user" );
						}
					}

					// Update Users Meta
					$tmp_user_meta = isset( $current_tmp_user['meta'] ) ? $current_tmp_user['meta'] : array();
					$pkg_user_meta = isset( $current_pkg_user['meta'] ) ? $current_pkg_user['meta'] : array();
					Users::update_package_user_meta( $pkg_user_meta, $tmp_user_meta, $user_id, "for `" . $_exist_DB['data']['user_login'] . "` user meta" );
				}
			}
			$x_pkg ++;
		}

		// Remove Filter Send Mail
		remove_filter( 'send_email_change_email', '__return_false' );
		remove_filter( 'send_password_change_email', '__return_false' );
	}

	/**
	 * Update Users Meta ( work in Package Update )
	 *
	 * @param $pkg_user_meta
	 * @param $tmp_user_meta
	 * @param $user_id
	 * @param $type
	 */
	public static function update_package_user_meta( $pkg_user_meta, $tmp_user_meta, $user_id, $type ) {

		// Get Default User Meta in WordPress
		$default_user_meta = Users::get_default_user_meta();

		// Remove User Meta
		foreach ( $tmp_user_meta as $meta_key => $meta_value ) {
			$sanitize_meta_key = Users::sanitize_meta_name( $meta_key );
			if ( ! isset( $pkg_user_meta[ $meta_key ] ) and ! in_array( $sanitize_meta_key, $default_user_meta ) ) {
				delete_user_meta( $user_id, $sanitize_meta_key );
				install::add_detail_log( CLI::_e( 'package', 'manage_item_red', array( "[work]" => "Removed", "[key]" => $sanitize_meta_key, "[type]" => $type ) ) );
			}
		}

		// Add Or Edit User Meta
		foreach ( $pkg_user_meta as $meta_key => $meta_value ) {
			$sanitize_meta_key = Users::sanitize_meta_name( $meta_key );

			// Check Add Meta
			if ( ! isset( $tmp_user_meta[ $meta_key ] ) and Users::exist_user_meta( $user_id, $sanitize_meta_key ) === false ) {
				add_user_meta( $user_id, $sanitize_meta_key, $meta_value );
				install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Added", "[key]" => $sanitize_meta_key, "[type]" => $type ) ) );
			}

			// Update User Meta
			$before_value = isset( $tmp_user_meta[ $meta_key ] ) ? $tmp_user_meta[ $meta_key ] : get_user_meta( $user_id, $sanitize_meta_key, true );
			if ( $meta_value !== $before_value ) {
				update_user_meta( $user_id, $sanitize_meta_key, $meta_value );
				install::add_detail_log( CLI::_e( 'package', 'manage_item_blue', array( "[work]" => "Updated", "[key]" => $sanitize_meta_key, "[type]" => $type ) ) );
			}
		}
	}

	/**
	 * Check Exist User in WordPress Database
	 *
	 * @param array $user
	 * @return array|bool
	 * @run after_wp_load
	 */
	public static function check_exist_user( $user = array() ) {
		// Default
		$return = array( 'status' => false, 'data' => '' );

		// Exist Username
		if ( isset( $user['user_login'] ) ) {
			$get = get_user_by( 'login', $user['user_login'] );
			if ( $get ) {
				$return = array( 'status' => true, 'key' => 'user_login', 'ID' => $get->ID );
			}
		}

		// Exist User Email
		if ( isset( $user['user_email'] ) and ! isset( $user_id ) ) {
			$get = get_user_by( 'email', $user['user_email'] );
			if ( $get ) {
				$return = array( 'status' => true, 'key' => 'user_email', 'ID' => $get->ID );
			}
		}

		// Get User data if Exist
		if ( $return['status'] === true and isset( $return['ID'] ) ) {
			$return['data'] = self::get_userdata( $return['ID'] );
		}

		return $return;
	}

}