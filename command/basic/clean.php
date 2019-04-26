<?php

use WP_CLI_APP\Utility\Optimize;


/**
 * Clean and Full Optimize Wordpress
 *
 * [<area>]
 * : area of clean
 *
 * ----- List ----
 * draft -> Clean all post drafts.
 * revision -> Clean all post revisions.
 * trash -> Clean all trashed posts.
 * auto-draft -> Clean all auto-draft posts.
 * comment -> Clean all [spam, trash, unapproved, pingback, trackbacks] Comment
 * meta -> Clean all post and comment meta data.
 * transients -> Clean all expired transients
 * options -> Clean Wordpress Options (remove all transients, user session, jetpack option)
 * --------------
 *
 * ## DOCUMENT
 *
 *      https://realwordpress.github.io/wp-cli-application/#basic-remove
 *
 *
 * ## EXAMPLES
 *
 *     $ wp app clean
 *      Run Clean of Wordpress
 *
 *      $ wp app clean draft
 *      remove of all draft posts
 *
 */
function wp_cli_app_basic_clean( $args, $assoc_args ) {

	if(!isset($args[0])) {

		//Clean all post revisions
		$post_revisions = Optimize::clear_post_revisions();
		WP_CLI::log( "Step 1/10 : Clean all post revisions." . ( $post_revisions > 0 ? ' [' . number_format( $post_revisions ) . ' Post' . ( $post_revisions > 1 ? 's' : '' ) . ']' : '' ) );


		//Clean all auto-draft posts
		$post_auto_draft = Optimize::clean_auto_draft();
		WP_CLI::log( "Step 2/10 : Clean all auto-draft posts." . ( $post_auto_draft > 0 ? ' [' . number_format( $post_auto_draft ) . ' Post' . ( $post_auto_draft > 1 ? 's' : '' ) . ']' : '' ) );


		//Clean all trashed posts
		$post_trashed = Optimize::clean_all_trashed_post();
		WP_CLI::log( "Step 3/10 : Clean all trashed posts." . ( $post_trashed > 0 ? ' [' . number_format( $post_trashed ) . ' Post' . ( $post_trashed > 1 ? 's' : '' ) . ']' : '' ) );


		//Remove Spam Comments
		$span_comment = Optimize::remove_spam_comments();
		WP_CLI::log( "Step 4/10 : Remove Spam comments." . ( $span_comment > 0 ? ' [' . number_format( $span_comment ) . ' Comment' . ( $span_comment > 1 ? 's' : '' ) . ']' : '' ) );


		//Remove Trash Comment
		$trash_comment = Optimize::remove_trash_comments();
		WP_CLI::log( "Step 5/10 : Remove Trashed comments." . ( $trash_comment > 0 ? ' [' . number_format( $trash_comment ) . ' Comment' . ( $trash_comment > 1 ? 's' : '' ) . ']' : '' ) );


		//Remove unapproved comments
		$unapprove_comment = Optimize::remove_unapproved_comment();
		WP_CLI::log( "Step 6/10 : Remove unapproved comments." . ( $unapprove_comment > 0 ? ' [' . number_format( $unapprove_comment ) . ' Comment' . ( $unapprove_comment > 1 ? 's' : '' ) . ']' : '' ) );


		//Remove pingbacks and trackbacks
		$rm_ping  = Optimize::remove_pingback_comments();
		$rm_track = Optimize::remove_trackbacks();
		WP_CLI::log( "Step 7/10 : Clean pingbacks and trackbacks." );


		//Remove Post and Comment Metadata
		$rm_post_meta    = Optimize::clean_post_meta_data();
		$rm_comment_meta = Optimize::clean_comment_meta_data();
		WP_CLI::log( "Step 8/10 : Clean Posts and Comments Metadata." );


		//Clean orphaned relationship data
		$orphan = Optimize::clean_orphaned_data();
		WP_CLI::log( "Step 9/10 : Clean orphaned relationship data." );


		//Remove expired transient options
		$transient = Optimize::remove_expired_transient();
		WP_CLI::log( "Step 10/10 : Remove expired transient options." );


		//success
		WP_CLI::success( "Wordpress is Cleaned successfully." );

	} else {

		$validation = array("draft", "revision", "trash", "auto-draft", "comment", "meta", "transients", "options");

		//Check Validation Value
		if( ! in_array($args[0], $validation) ) {
			WP_CLI::error( "`".$args[0]."` is not validation Area, Show area list `wp app clean --help`" );
			return;
		}

		switch ($args[0]) {

			case "draft":
				$post_draft = Optimize::remove_from_wp_posts('post_type', 'draft');
				WP_CLI::success( "Clean all post drafts." . ( $post_draft > 0 ? ' [' . number_format( $post_draft ) . ' Post' . ( $post_draft > 1 ? 's' : '' ) . ']' : '' ) );
				break;

			case "revision":
				$post_revisions = Optimize::clear_post_revisions();
				WP_CLI::success( "Clean all post revisions." . ( $post_revisions > 0 ? ' [' . number_format( $post_revisions ) . ' Post' . ( $post_revisions > 1 ? 's' : '' ) . ']' : '' ) );
				break;

			case "trash":
				$post_trashed = Optimize::clean_all_trashed_post();
				WP_CLI::success( "Clean all trashed posts." . ( $post_trashed > 0 ? ' [' . number_format( $post_trashed ) . ' Post' . ( $post_trashed > 1 ? 's' : '' ) . ']' : '' ) );
				break;

			case "auto-draft":
				$post_auto_draft = Optimize::clean_auto_draft();
				WP_CLI::success( "Clean all auto-draft posts." . ( $post_auto_draft > 0 ? ' [' . number_format( $post_auto_draft ) . ' Post' . ( $post_auto_draft > 1 ? 's' : '' ) . ']' : '' ) );
				break;

			case "comment":
				//Remove Spam Comments
				$span_comment = Optimize::remove_spam_comments();
				WP_CLI::log( "Step 1/4 : Remove Spam comments." . ( $span_comment > 0 ? ' [' . number_format( $span_comment ) . ' Comment' . ( $span_comment > 1 ? 's' : '' ) . ']' : '' ) );

				//Remove Trash Comment
				$trash_comment = Optimize::remove_trash_comments();
				WP_CLI::log( "Step 2/4 : Remove Trashed comments." . ( $trash_comment > 0 ? ' [' . number_format( $trash_comment ) . ' Comment' . ( $trash_comment > 1 ? 's' : '' ) . ']' : '' ) );

				//Remove unapproved comments
				$unapprove_comment = Optimize::remove_unapproved_comment();
				WP_CLI::log( "Step 3/4 : Remove unapproved comments." . ( $unapprove_comment > 0 ? ' [' . number_format( $unapprove_comment ) . ' Comment' . ( $unapprove_comment > 1 ? 's' : '' ) . ']' : '' ) );

				//Remove pingbacks and trackbacks
				$rm_ping  = Optimize::remove_pingback_comments();
				$rm_track = Optimize::remove_trackbacks();
				WP_CLI::log( "Step 4/4 : Clean pingbacks and trackbacks." );

				WP_CLI::success( "Clean Wordpress Comments Completely." );
				break;

			case "meta":
				$rm_post_meta    = Optimize::clean_post_meta_data();
				$rm_comment_meta = Optimize::clean_comment_meta_data();
				$orphan = Optimize::clean_orphaned_data();

				WP_CLI::success( "Clean all Metadata successfully." );
				break;

			case "transients":
				$transient = Optimize::remove_expired_transient();
				WP_CLI::success( "Remove expired transient options successfully." );
				break;

			case "options":
				$options = Optimize::clean_options();
				WP_CLI::success( "Options table Cleaned successfully." );
				break;

		}

	}
}