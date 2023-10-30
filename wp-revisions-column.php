<?php
/**
 * Plugin Name: WP Revisions Column
 * Plugin URI:  https://github.com/kitestring-studio/wp-revisions-column
 * Description: Adds a revisions count column to the post editor list in the WordPress admin dashboard.
 * Version:     1.0
 * Author:      Kitestring Studio
 * Author URI:  https://kitestring.studio/
 * License:     GPL-2.0+
 */

class WP_Revisions_Column {
	private static $instance = null;

	private function __construct() {
		add_action( 'admin_head', [ $this, 'add_inline_styles' ] );
		add_filter( 'manage_posts_columns', [ $this, 'add_revisions_column' ] );
		add_filter( 'manage_pages_columns', [ $this, 'add_revisions_column' ] );
		add_action( 'manage_posts_custom_column', [ $this, 'show_revisions_count' ], 10, 2 );
		add_action( 'manage_pages_custom_column', [ $this, 'show_revisions_count' ], 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', [ $this, 'register_sortable_revisions_column' ] );
		add_filter( 'manage_edit-page_sortable_columns', [ $this, 'register_sortable_revisions_column' ] );
		add_action( 'pre_get_posts', [ $this, 'sort_by_revisions' ] );
	}

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new WP_Revisions_Column();
		}

		return self::$instance;
	}

	public function add_revisions_column( $columns ) {
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			$columns['revisions'] = 'Revisions';
		}

		return $columns;
	}

	public function show_revisions_count( $column_name, $post_id ) {
		if ( 'revisions' === $column_name ) {
			$revisions = wp_get_post_revisions( $post_id );
			echo count( $revisions );
		}
	}

	public function register_sortable_revisions_column( $columns ) {
		$columns['revisions'] = 'revisions';

		return $columns;
	}

	public function sort_by_revisions( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->get( 'orderby' ) === 'revisions' ) {
			$query->set( 'meta_key', '_edit_last' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	public function add_inline_styles() {
		echo '<style>
            .manage-column.column-revisions {
                width: 80px !important;
                max-width: 80px !important;
            }
        </style>';
	}
}

// Initialize the singleton instance
WP_Revisions_Column::getInstance();
