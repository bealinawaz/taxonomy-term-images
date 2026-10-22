<?php
/**
 * Template functions for themes.
 *
 * @package Taxonomy_Term_Images
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns a term's image attachment ID.
 *
 * @param WP_Term|int|null $term Term object or ID. Defaults to the term of the current archive page.
 * @return int Attachment ID, or 0 when the term has no image.
 */
function taxonomy_term_images_get_id( $term = null ) {
	return Taxonomy_Term_Images::get_image_id( $term );
}

/**
 * Returns the URL of a term's image.
 *
 * @param WP_Term|int|null $term Term object or ID. Defaults to the term of the current archive page.
 * @param string|int[]     $size Image size name or array of width and height.
 * @return string Image URL, or an empty string.
 */
function taxonomy_term_images_get_url( $term = null, $size = 'full' ) {
	$id  = Taxonomy_Term_Images::get_image_id( $term );
	$url = $id ? wp_get_attachment_image_url( $id, $size ) : false;
	return $url ? $url : '';
}

/**
 * Returns an img tag for a term's image, with srcset, sizes, and alt text.
 * The alt text falls back to the term name when the image has none.
 *
 * @param WP_Term|int|null $term Term object or ID. Defaults to the term of the current archive page.
 * @param string|int[]     $size Image size name or array of width and height.
 * @param array            $attr Extra attributes for the img tag.
 * @return string Image HTML, or an empty string.
 */
function taxonomy_term_images_get_html( $term = null, $size = 'thumbnail', $attr = array() ) {
	return Taxonomy_Term_Images::get_image_html( $term, $size, $attr );
}
