<?php
/**
 * Plugin Name:       Taxonomy Term Images – Add Images to Categories, Tags & Custom Taxonomies
 * Plugin URI:        https://github.com/bealinawaz/taxonomy-term-images
 * Description:       Add an image field to categories, tags, and custom taxonomy terms using the media library. Show term images with a shortcode, a block, or template functions.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Ali Nawaz
 * Author URI:        https://bealinawaz.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       taxonomy-term-images
 *
 * @package Taxonomy_Term_Images
 */

defined( 'ABSPATH' ) || exit;

define( 'TAXONOMY_TERM_IMAGES_VERSION', '1.0.0' );
define( 'TAXONOMY_TERM_IMAGES_FILE', __FILE__ );
define( 'TAXONOMY_TERM_IMAGES_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-taxonomy-term-images-settings.php';
require_once __DIR__ . '/includes/class-taxonomy-term-images.php';
require_once __DIR__ . '/includes/template-functions.php';

Taxonomy_Term_Images::init();

if ( is_admin() ) {
	require_once __DIR__ . '/includes/class-taxonomy-term-images-admin.php';
	Taxonomy_Term_Images_Admin::init();
}
