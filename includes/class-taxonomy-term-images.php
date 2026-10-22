<?php
/**
 * Term meta, shortcode, block, and Open Graph image.
 *
 * @package Taxonomy_Term_Images
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stores an image attachment ID for each term and displays it on the front end.
 */
final class Taxonomy_Term_Images {

	const META_KEY = 'ttimg_image_id';
	const BLOCK    = 'taxonomy-term-images/term-image';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		// Late, so custom taxonomies registered on init are available.
		add_action( 'init', array( __CLASS__, 'register' ), 99 );
		add_action( 'wp_head', array( __CLASS__, 'og_image' ), 5 );
	}

	/**
	 * Registers the term meta, shortcodes, and block.
	 */
	public static function register() {
		foreach ( Taxonomy_Term_Images_Settings::enabled_taxonomies() as $taxonomy ) {
			register_term_meta(
				$taxonomy,
				self::META_KEY,
				array(
					'type'              => 'integer',
					'description'       => __( 'Attachment ID of the term image.', 'taxonomy-term-images' ),
					'single'            => true,
					'default'           => 0,
					'sanitize_callback' => array( __CLASS__, 'sanitize_image_id' ),
					'auth_callback'     => array( __CLASS__, 'can_edit_term' ),
					'show_in_rest'      => true,
				)
			);
		}

		add_shortcode( 'taxonomy_term_image', array( __CLASS__, 'shortcode' ) );
		if ( ! shortcode_exists( 'term_image' ) ) {
			add_shortcode( 'term_image', array( __CLASS__, 'shortcode' ) );
		}

		wp_register_script(
			'taxonomy-term-images-block',
			TAXONOMY_TERM_IMAGES_URL . 'assets/block/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			TAXONOMY_TERM_IMAGES_VERSION,
			true
		);
		wp_set_script_translations( 'taxonomy-term-images-block', 'taxonomy-term-images' );
		register_block_type(
			dirname( TAXONOMY_TERM_IMAGES_FILE ) . '/assets/block',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
			)
		);
	}

	/**
	 * Keeps only IDs of image attachments.
	 *
	 * @param mixed $value Submitted value.
	 * @return int Attachment ID, or 0.
	 */
	public static function sanitize_image_id( $value ) {
		$id = absint( $value );
		return $id && wp_attachment_is_image( $id ) ? $id : 0;
	}

	/**
	 * Whether the current user may change a term's image.
	 *
	 * @param bool   $allowed   Whether the user can add the meta.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Term ID.
	 * @return bool
	 */
	public static function can_edit_term( $allowed, $meta_key, $object_id ) {
		return current_user_can( 'edit_term', (int) $object_id );
	}

	/**
	 * Whether a taxonomy has the image field turned on.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return bool
	 */
	public static function is_enabled( $taxonomy ) {
		return in_array( $taxonomy, Taxonomy_Term_Images_Settings::enabled_taxonomies(), true );
	}

	/**
	 * Saves or removes a term's image.
	 *
	 * @param int $term_id  Term ID.
	 * @param int $image_id Attachment ID, or 0 to remove.
	 * @return bool
	 */
	public static function set_image_id( $term_id, $image_id ) {
		$image_id = self::sanitize_image_id( $image_id );
		if ( ! $image_id ) {
			return delete_term_meta( $term_id, self::META_KEY );
		}
		return (bool) update_term_meta( $term_id, self::META_KEY, $image_id );
	}

	/**
	 * Resolves a term from a WP_Term, ID, slug, or the current archive.
	 *
	 * @param WP_Term|int|string|null $term     Term, term ID, slug, or null for the current archive term.
	 * @param string                  $taxonomy Taxonomy, needed for slugs.
	 * @return WP_Term|null
	 */
	public static function resolve_term( $term = null, $taxonomy = '' ) {
		if ( $term instanceof WP_Term ) {
			return $term;
		}
		if ( null === $term || '' === $term ) {
			$queried = get_queried_object();
			return $queried instanceof WP_Term ? $queried : null;
		}
		if ( is_numeric( $term ) ) {
			$found = get_term( (int) $term, $taxonomy ? $taxonomy : '' );
		} else {
			$found = get_term_by( 'slug', (string) $term, $taxonomy ? $taxonomy : 'category' );
		}
		return $found instanceof WP_Term ? $found : null;
	}

	/**
	 * Image attachment ID for a term.
	 *
	 * @param WP_Term|int|string|null $term     Term, ID, slug, or null for the current archive term.
	 * @param string                  $taxonomy Taxonomy, needed for slugs.
	 * @return int
	 */
	public static function get_image_id( $term = null, $taxonomy = '' ) {
		$term = self::resolve_term( $term, $taxonomy );
		if ( ! $term ) {
			return 0;
		}
		$id = (int) get_term_meta( $term->term_id, self::META_KEY, true );

		// WooCommerce stores its own product category thumbnails.
		if ( ! $id && 'product_cat' === $term->taxonomy ) {
			$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		}
		if ( $id && ! wp_attachment_is_image( $id ) ) {
			$id = 0;
		}

		/**
		 * Filters a term's image attachment ID.
		 *
		 * @param int     $id   Attachment ID, or 0.
		 * @param WP_Term $term Term.
		 */
		return (int) apply_filters( 'taxonomy_term_images_image_id', $id, $term );
	}

	/**
	 * Image HTML for a term, with srcset and alt text.
	 *
	 * @param WP_Term|int|string|null $term Term, ID, slug, or null for the current archive term.
	 * @param string|int[]            $size Image size.
	 * @param array                   $attr Image attributes.
	 * @param string                  $taxonomy Taxonomy, needed for slugs.
	 * @return string
	 */
	public static function get_image_html( $term = null, $size = 'thumbnail', $attr = array(), $taxonomy = '' ) {
		$term = self::resolve_term( $term, $taxonomy );
		$id   = $term ? self::get_image_id( $term ) : 0;
		if ( ! $id ) {
			return '';
		}
		$attr = is_array( $attr ) ? $attr : array();
		if ( ! isset( $attr['alt'] ) && '' === trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) ) {
			$attr['alt'] = $term->name;
		}
		$attr['class'] = trim( 'ttimg-image ' . ( isset( $attr['class'] ) ? $attr['class'] : '' ) );
		return wp_get_attachment_image( $id, $size, false, $attr );
	}

	/**
	 * Normalizes an image size from a shortcode or block attribute.
	 *
	 * @param string $size Size name.
	 * @return string
	 */
	private static function size( $size ) {
		$size = sanitize_key( (string) $size );
		return in_array( $size, get_intermediate_image_sizes(), true ) || 'full' === $size ? $size : 'medium';
	}

	/**
	 * Shortcode: [term_image term="slug-or-id" taxonomy="category" size="medium" link="no" class=""].
	 * Without a term it uses the term of the current archive page.
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'term'     => '',
				'taxonomy' => '',
				'size'     => 'medium',
				'link'     => 'no',
				'class'    => '',
			),
			$atts,
			'term_image'
		);

		$taxonomy = sanitize_key( $atts['taxonomy'] );
		$term     = self::resolve_term( sanitize_text_field( $atts['term'] ), $taxonomy );
		if ( ! $term ) {
			return '';
		}
		$classes = array_filter( array_map( 'sanitize_html_class', explode( ' ', (string) $atts['class'] ) ) );
		$html    = self::get_image_html( $term, self::size( $atts['size'] ), $classes ? array( 'class' => implode( ' ', $classes ) ) : array() );
		if ( '' === $html ) {
			return '';
		}
		if ( in_array( strtolower( (string) $atts['link'] ), array( '1', 'yes', 'true' ), true ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$html = '<a href="' . esc_url( $link ) . '">' . $html . '</a>';
			}
		}
		return $html;
	}

	/**
	 * Renders the Term Image block for the term being viewed.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_block( $attributes ) {
		$term = self::resolve_term();
		if ( ! $term ) {
			return '';
		}
		$html = self::get_image_html( $term, self::size( isset( $attributes['size'] ) ? $attributes['size'] : 'large' ) );
		if ( '' === $html ) {
			return '';
		}
		if ( ! empty( $attributes['linkToTerm'] ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$html = '<a href="' . esc_url( $link ) . '">' . $html . '</a>';
			}
		}
		return '<figure ' . get_block_wrapper_attributes( array( 'class' => 'ttimg-block' ) ) . '>' . $html . '</figure>';
	}

	/**
	 * Whether an SEO plugin that writes its own Open Graph tags is active.
	 *
	 * @return bool
	 */
	public static function seo_plugin_active() {
		$active = defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| class_exists( 'RankMath', false )
			|| defined( 'SEOPRESS_VERSION' )
			|| defined( 'AIOSEO_VERSION' )
			|| function_exists( 'aioseo' );
		/**
		 * Filters whether an SEO plugin handles Open Graph tags, which turns off the og:image output.
		 *
		 * @param bool $active Whether a known SEO plugin is active.
		 */
		return (bool) apply_filters( 'taxonomy_term_images_seo_plugin_active', $active );
	}

	/**
	 * Prints og:image tags on term archives when enabled and no SEO plugin handles them.
	 */
	public static function og_image() {
		if ( ! Taxonomy_Term_Images_Settings::get()['og_image'] || ! ( is_category() || is_tag() || is_tax() ) || self::seo_plugin_active() ) {
			return;
		}
		$term = self::resolve_term();
		if ( ! $term || ! self::is_enabled( $term->taxonomy ) ) {
			return;
		}
		$id    = self::get_image_id( $term );
		$image = $id ? wp_get_attachment_image_src( $id, 'large' ) : false;
		if ( ! $image ) {
			return;
		}
		$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image[0] ) );
		if ( $image[1] && $image[2] ) {
			printf( '<meta property="og:image:width" content="%d" />' . "\n", (int) $image[1] );
			printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $image[2] );
		}
		printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( '' !== $alt ? $alt : $term->name ) );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image[0] ) );
	}
}
