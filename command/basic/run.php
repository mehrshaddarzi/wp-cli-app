<?php

use WP_CLI_APP\Utility\CLI;
use WP_CLI_APP\Utility\Wordpress;

/**
 * Run wordpress Site in Browser
 *
 *
 * [<type>]
 * : type Of page
 *
 * ----- List ----
 * login      -> Show WordPress login page
 * admin      -> view admin dashboard (if not user login, automatic set session first user ID in WordPress)
 * post       -> show post or page by id or slug `wp app run post 1`
 * term       -> show terms contain category,post_tag, and all custom taxonomy by id or slug `wp app run term 1`
 * 404        -> show 404 WordPress page
 * Custom Url -> Show Custom Url in browser `wp app run /contact`
 * --------------
 *
 * [<ID>]
 * : page id or page slug
 *
 * [--user=<value>]
 * : For the Admin Area, enter user ID or user_email or user_login
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app run
 *
 */
function wp_cli_app_basic_run( $args, $assoc_args ) {
	global $wpdb;

	$home = rtrim( get_option( "home" ), "/" );

	//Default show homeUrl
	if ( ! isset( $args[0] ) ) {

		CLI::Browser( $home );
	} else {


		$validation = array( "login", "admin", "post", "term", "404");
		$items      = array(
			array(
				'name'        => 'login',
				'example'     => 'wp app run login',
				'description' => 'Show WordPress login page',
			),
			array(
				'name'        => 'admin',
				'example'     => 'wp app run admin --user=1',
				'description' => 'show admin dashboard ,automatic login user if not the session',
			),
			array(
				'name'        => 'post',
				'example'     => 'wp app run post 1',
				'description' => 'show Post or Page by ID or slug',
			),
			array(
				'name'        => 'term',
				'example'     => 'wp app run term 1',
				'description' => 'show terms contain category,tag,.. by id or slug',
			),
			array(
				'name'        => '404',
				'example'     => 'wp app run 404',
				'description' => 'show 404 WordPress page',
			),
			array(
				'name'        => 'Custom Url',
				'example'     => 'wp app "/archive"',
				'description' => 'Show Custom Url in browser',
			),
		);

		switch ( $args[0] ) {

			case "login":
				CLI::Browser( wp_login_url() );
				break;

			case "404":
				$latest_post  = get_posts( "numberposts=1" );
				$generate_404 = get_the_permalink( $latest_post[0]->ID ) . "404Page";
				CLI::Browser( $generate_404 );
				break;

			case "post":
				//Require second parameter
				if ( ! isset( $args[1] ) || trim( $args[1] ) == "" ) {
					WP_CLI::error( "Please enter Post ID or Post Slug, for example: `wp app run post 1`" );
				}

				//Check Numeric id or slug
				if ( is_numeric( $args[1] ) ) {
					//Area For Post ID
					//Check post id is exist
					if ( 'publish' == get_post_status( $args[1] ) ) {
						CLI::Browser( get_the_permalink( $args[1] ) );
					} else {
						WP_CLI::error( "Post with this ID is not found in WordPress." );
					}

				} else {
					//Area For Post slug
					//Check post slug is exist
					$my_posts = get_posts( array(
						'name'        => $args[1],
						'post_status' => 'publish',
						'numberposts' => 1
					) );
					if ( $my_posts ) {
						CLI::Browser( get_the_permalink( $my_posts[0]->ID ) );
					} else {
						WP_CLI::error( "Post with this Slug is not found in WordPress." );
					}
				}
				break;

			case "term":
				//Require second parameter
				if ( ! isset( $args[1] ) || trim( $args[1] ) == "" ) {
					WP_CLI::error( "Please enter Term ID or Term Slug, for example: `wp app run term 1`" );
				}

				//Go to Term Link
				if ( is_numeric( $args[1] ) ) {
					$term  = (int) $args[1];
					$terms = get_term_link( $term );
					if ( is_wp_error( $terms ) ) {
						WP_CLI::error( "Term is not found." );
					} else {
						CLI::Browser( $terms );
					}
				} else {
					$term = term_exists( trim( $args[1] ) );
					if ( $term !== 0 && $term !== null ) {
						$terms = get_terms( array(
							'slug'       => trim( $args[1] ),
							'number'     => 1,
							'hide_empty' => false
						) );
						if ( is_wp_error( $terms ) || empty( $terms ) ) {
							WP_CLI::error( "Term is not found." );
						} else {
							CLI::Browser( get_term_link( $terms[0]->term_id ) );
						}
					}
				}
				break;

			case "admin":

				//Get User id
				if ( isset( $assoc_args['user'] ) and $assoc_args['user'] != "" ) {
					$user = Wordpress::get_user_info( trim( $assoc_args['user'] ) );
					if ( $user != false ) {
						$user_id = $user->ID;
					} else {
						WP_CLI::error( "User is not found." );
					}
				} else {
					$get_admin_user = $wpdb->get_row( "SELECT * FROM {$wpdb->users} ORDER BY `ID` ASC LIMIT 1", ARRAY_A );
					$user_id        = $get_admin_user['ID'];
				}

				//Please Wait
				CLI::PleaseWait();

				//Login User
				Wordpress::set_current_user( $user_id, admin_url( "index.php" ) );
				break;

			default :
				$url = trim( $args[0] );
				//go to Page
				CLI::Browser( $home . "/" . ltrim( $url, "/" ) );
		}
	}

}
