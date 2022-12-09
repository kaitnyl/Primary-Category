<?php
/**
 * Plugin Name: Primary Category for Posts
 * Description: Set a primary category for posts
 * Author: Kaitlyn McDonald
 * Version: 1.0
 *
 * @package 10up Code Exercise
 */

/**
 * Class Primary_Category
 */
class Primary_Category {

	const META_KEY  = 'primary_category';
	const HTML_KEY  = 'primary-category';
	const NONCE_KEY = 'primary_category_nonce';

	/**
	 * WordPress Hooks
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_value' ) );
	}

	/**
	 * Add meta box to post screen
	 */
	public function add_meta_box() {
		add_meta_box( self::HTML_KEY, 'Primary Category', array( $this, 'meta_box_html' ), 'post', 'side' );
	}

	/**
	 * Renders the HTML output for our meta box.
	 *
	 * @param WP_Post $post The post.
	 */
	public function meta_box_html( $post ) {
		$categories = get_categories(
			array(
				'hide_empty' => false,
				'taxonomy'   => 'category',
			)
		);

		$saved = (int) get_post_meta( $post->ID, self::META_KEY, true );

		// Always default to saving "Uncategorized" as default.
		if ( 0 === $saved ) {
			$saved = 1;
		}

		echo '<select name="' . esc_attr( self::HTML_KEY ) . '" id="' . esc_attr( self::META_KEY ) . '">';

		foreach ( $categories as $category ) {
			$selected_html = '';
			if ( $category->cat_ID === $saved ) {
				$selected_html = ' selected';
			}

			echo '<option value="' . esc_attr( $category->cat_ID ) . '"' . esc_attr( $selected_html ) . '>' . esc_attr( $category->name ) . '</option>';
		}
		echo '</select>';

		wp_nonce_field( self::NONCE_KEY . '_' . $post->ID, self::NONCE_KEY );
	}

	/**
	 * Save the selected value when post is saved.
	 *
	 * @param integer $post_id The post ID.
	 *
	 * @return bool
	 */
	public function save_value( $post_id ) {
		if ( ! isset( $_POST[ self::HTML_KEY ] ) || ! isset( $_POST[ self::NONCE_KEY ] ) ) { // Input var.
			return false;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_KEY ] ) ), self::NONCE_KEY . '_' . $post_id ) ) { // Input var.
			return false;
		};

		return update_post_meta( $post_id, self::META_KEY, sanitize_text_field( wp_unslash( $_POST[ self::HTML_KEY ] ) ) ); // Input var.
	}

	/**
	 * Get posts by primary category ID.
	 *
	 * @param integer $cat_id The category ID.
	 *
	 * @return WP_Query
	 */
	public static function get_posts( $cat_id = 0 ) {
		$args = array(
			'meta_key'   => self::META_KEY, // Slow query.
			'meta_value' => $cat_id, // Slow query.
		);

		return new WP_Query( $args );
	}

}

new Primary_Category();
