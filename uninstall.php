<?php
/**
 * Removes the plugin settings and every term image reference.
 * The images stay in the media library.
 *
 * @package Taxonomy_Term_Images
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'taxonomy_term_images_settings' );
delete_metadata( 'term', 0, 'ttimg_image_id', '', true );
