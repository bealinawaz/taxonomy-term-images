<?php
/**
 * Plugin settings.
 *
 * @package Taxonomy_Term_Images
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads and validates the plugin settings.
 */
final class Taxonomy_Term_Images_Settings {

	const OPTION = 'taxonomy_term_images_settings';
	const GROUP  = 'taxonomy_term_images';

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'taxonomies' => array( 'category', 'post_tag' ),
			'og_image'   => false,
		);
	}

	/**
	 * Current settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		return array_replace( self::defaults(), is_array( $saved ) ? $saved : array() );
	}

	/**
	 * Taxonomies that can get an image field: public taxonomies with an admin screen.
	 *
	 * @return WP_Taxonomy[] Keyed by taxonomy name.
	 */
	public static function available_taxonomies() {
		$taxonomies = get_taxonomies(
			array(
				'public'  => true,
				'show_ui' => true,
			),
			'objects'
		);
		unset( $taxonomies['post_format'], $taxonomies['nav_menu'], $taxonomies['link_category'], $taxonomies['wp_pattern_category'] );
		return $taxonomies;
	}

	/**
	 * Taxonomies with the image field turned on.
	 *
	 * @return string[]
	 */
	public static function enabled_taxonomies() {
		$taxonomies = (array) self::get()['taxonomies'];
		/**
		 * Filters the taxonomies that get an image field.
		 *
		 * @param string[] $taxonomies Taxonomy names.
		 */
		$taxonomies = (array) apply_filters( 'taxonomy_term_images_taxonomies', $taxonomies );
		return array_values( array_filter( array_map( 'sanitize_key', $taxonomies ), 'taxonomy_exists' ) );
	}

	/**
	 * Sanitizes the settings form.
	 *
	 * @param mixed $input Submitted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input     = is_array( $input ) ? $input : array();
		$available = array_keys( self::available_taxonomies() );
		$chosen    = isset( $input['taxonomies'] ) && is_array( $input['taxonomies'] ) ? array_map( 'sanitize_key', $input['taxonomies'] ) : array();

		return array(
			'taxonomies' => array_values( array_intersect( $chosen, $available ) ),
			'og_image'   => ! empty( $input['og_image'] ),
		);
	}
}
