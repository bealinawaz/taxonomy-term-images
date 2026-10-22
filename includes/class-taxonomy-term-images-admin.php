<?php
/**
 * Term screens, list table column, and settings page.
 *
 * @package Taxonomy_Term_Images
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin integration.
 */
final class Taxonomy_Term_Images_Admin {

	const PAGE        = 'taxonomy-term-images';
	const NONCE       = 'ttimg_term_image_nonce';
	const FIELD       = 'ttimg_image_id';
	const AUTHOR_URL  = 'https://bealinawaz.com/';
	const CONTACT_URL = 'https://bealinawaz.com/contact-me/';
	const DONATE_URL  = 'https://www.buymeacoffee.com/bealinawaz';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_term_hooks' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'created_term', array( __CLASS__, 'save' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'save' ), 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( TAXONOMY_TERM_IMAGES_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
	}

	/**
	 * Adds the form fields and list column for each enabled taxonomy.
	 */
	public static function register_term_hooks() {
		foreach ( Taxonomy_Term_Images_Settings::enabled_taxonomies() as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( __CLASS__, 'add_field' ) );
			add_action( $taxonomy . '_edit_form_fields', array( __CLASS__, 'edit_field' ) );
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( __CLASS__, 'add_column' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( __CLASS__, 'render_column' ), 10, 3 );
		}
	}

	/**
	 * Loads the media library and field script on enabled term screens, and styles on the settings page.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		if ( 'settings_page_' . self::PAGE === $hook ) {
			wp_enqueue_style( 'taxonomy-term-images-admin', TAXONOMY_TERM_IMAGES_URL . 'assets/admin.css', array(), TAXONOMY_TERM_IMAGES_VERSION );
			return;
		}
		if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! Taxonomy_Term_Images::is_enabled( $screen->taxonomy ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'taxonomy-term-images-admin', TAXONOMY_TERM_IMAGES_URL . 'assets/admin.css', array(), TAXONOMY_TERM_IMAGES_VERSION );
		wp_enqueue_script( 'taxonomy-term-images-admin', TAXONOMY_TERM_IMAGES_URL . 'assets/admin.js', array( 'jquery', 'media-editor' ), TAXONOMY_TERM_IMAGES_VERSION, true );
		wp_localize_script(
			'taxonomy-term-images-admin',
			'taxonomyTermImages',
			array(
				'title'  => __( 'Choose a term image', 'taxonomy-term-images' ),
				'button' => __( 'Use this image', 'taxonomy-term-images' ),
			)
		);
	}

	/**
	 * Image picker markup shared by the add and edit forms.
	 *
	 * @param int $image_id Current attachment ID.
	 */
	private static function picker( $image_id ) {
		wp_nonce_field( 'ttimg_save_term_image', self::NONCE );
		?>
		<div class="ttimg-field">
			<input type="hidden" class="ttimg-field__input" id="ttimg-image-id" name="<?php echo esc_attr( self::FIELD ); ?>" value="<?php echo esc_attr( $image_id ? (string) $image_id : '' ); ?>" />
			<div class="ttimg-field__preview"><?php echo $image_id ? wp_get_attachment_image( $image_id, 'thumbnail' ) : ''; ?></div>
			<p class="ttimg-field__actions">
				<button type="button" class="button ttimg-field__select" aria-describedby="ttimg-image-description">
					<?php echo $image_id ? esc_html__( 'Replace image', 'taxonomy-term-images' ) : esc_html__( 'Select image', 'taxonomy-term-images' ); ?>
				</button>
				<button type="button" class="button-link button-link-delete ttimg-field__remove" <?php echo $image_id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove image', 'taxonomy-term-images' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Image field on the Add New term form.
	 */
	public static function add_field() {
		?>
		<div class="form-field term-ttimg-wrap">
			<span class="ttimg-label"><?php esc_html_e( 'Image', 'taxonomy-term-images' ); ?></span>
			<?php self::picker( 0 ); ?>
			<p id="ttimg-image-description"><?php esc_html_e( 'Shown by your theme, the Term Image block, or the [term_image] shortcode.', 'taxonomy-term-images' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Image field on the Edit term form.
	 *
	 * @param WP_Term $term Term being edited.
	 */
	public static function edit_field( $term ) {
		$image_id = (int) get_term_meta( $term->term_id, Taxonomy_Term_Images::META_KEY, true );
		?>
		<tr class="form-field term-ttimg-wrap">
			<th scope="row"><?php esc_html_e( 'Image', 'taxonomy-term-images' ); ?></th>
			<td>
				<?php self::picker( $image_id ); ?>
				<p class="description" id="ttimg-image-description"><?php esc_html_e( 'Shown by your theme, the Term Image block, or the [term_image] shortcode.', 'taxonomy-term-images' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saves the image when a term is created or edited from the term forms.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 */
	public static function save( $term_id, $tt_id, $taxonomy ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! Taxonomy_Term_Images::is_enabled( $taxonomy ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'ttimg_save_term_image' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}
		$image_id = isset( $_POST[ self::FIELD ] ) ? absint( wp_unslash( $_POST[ self::FIELD ] ) ) : 0;
		Taxonomy_Term_Images::set_image_id( $term_id, $image_id );
	}

	/**
	 * Adds an Image column after the checkbox column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function add_column( $columns ) {
		$image = array( 'ttimg_image' => '<span class="ttimg-column-title">' . esc_html__( 'Image', 'taxonomy-term-images' ) . '</span>' );
		if ( isset( $columns['cb'] ) ) {
			return array_slice( $columns, 0, 1, true ) + $image + array_slice( $columns, 1, null, true );
		}
		return $image + $columns;
	}

	/**
	 * Renders the Image column.
	 *
	 * @param string $content Column content.
	 * @param string $column  Column name.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public static function render_column( $content, $column, $term_id ) {
		if ( 'ttimg_image' !== $column ) {
			return $content;
		}
		$id = (int) get_term_meta( $term_id, Taxonomy_Term_Images::META_KEY, true );
		if ( ! $id ) {
			return '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html__( 'No image', 'taxonomy-term-images' ) . '</span>';
		}
		return wp_get_attachment_image( $id, array( 48, 48 ), false, array( 'class' => 'ttimg-column-image' ) );
	}

	/**
	 * Adds the settings page under Settings.
	 */
	public static function add_page() {
		add_options_page(
			__( 'Taxonomy Term Images', 'taxonomy-term-images' ),
			__( 'Term Images', 'taxonomy-term-images' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Registers the settings option.
	 */
	public static function register_setting() {
		register_setting(
			Taxonomy_Term_Images_Settings::GROUP,
			Taxonomy_Term_Images_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Taxonomy_Term_Images_Settings', 'sanitize' ),
			)
		);
	}

	/**
	 * Adds a Settings link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'taxonomy-term-images' ) . '</a>' );
		return $links;
	}

	/**
	 * Adds custom development and donation links under the plugin description.
	 *
	 * @param array  $links Plugin meta links.
	 * @param string $file  Plugin file.
	 * @return array
	 */
	public static function row_meta( $links, $file ) {
		if ( plugin_basename( TAXONOMY_TERM_IMAGES_FILE ) !== $file ) {
			return $links;
		}
		$links[] = '<a href="' . esc_url( self::CONTACT_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Custom development', 'taxonomy-term-images' ) . '</a>';
		$links[] = '<a href="' . esc_url( self::DONATE_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Buy me a coffee', 'taxonomy-term-images' ) . '</a>';
		return $links;
	}

	/**
	 * Renders the author box with support, custom work, and donation links.
	 */
	private static function render_support() {
		?>
		<div class="ttimg-support">
			<h2><?php esc_html_e( 'Need help or a custom version?', 'taxonomy-term-images' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: link to the author's website. */
					esc_html__( 'Taxonomy Term Images is free and built by Ali Nawaz, a WordPress and WooCommerce developer at %s. Need category landing pages, custom taxonomy templates, more term fields, or a brand or product category design built into your theme?', 'taxonomy-term-images' ),
					'<a href="' . esc_url( self::AUTHOR_URL ) . '" target="_blank" rel="noopener">bealinawaz.com</a>'
				);
				?>
			</p>
			<p class="ttimg-support__actions">
				<a class="button button-primary" href="<?php echo esc_url( self::CONTACT_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Hire me for custom work', 'taxonomy-term-images' ); ?></a>
				<a class="button" href="<?php echo esc_url( self::AUTHOR_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit bealinawaz.com', 'taxonomy-term-images' ); ?></a>
				<a class="button" href="<?php echo esc_url( self::DONATE_URL ); ?>" target="_blank" rel="noopener">&#9749; <?php esc_html_e( 'Buy me a coffee', 'taxonomy-term-images' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Renders the settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s       = Taxonomy_Term_Images_Settings::get();
		$enabled = Taxonomy_Term_Images_Settings::enabled_taxonomies();
		$name    = Taxonomy_Term_Images_Settings::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Taxonomy Term Images', 'taxonomy-term-images' ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( Taxonomy_Term_Images_Settings::GROUP ); ?>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Add an image field to', 'taxonomy-term-images' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Add an image field to', 'taxonomy-term-images' ); ?></legend>
								<?php foreach ( Taxonomy_Term_Images_Settings::available_taxonomies() as $taxonomy ) : ?>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[taxonomies][]" value="<?php echo esc_attr( $taxonomy->name ); ?>" <?php checked( in_array( $taxonomy->name, $enabled, true ) ); ?> />
										<?php echo esc_html( $taxonomy->labels->name ); ?>
										<code><?php echo esc_html( $taxonomy->name ); ?></code>
										<?php if ( 'product_cat' === $taxonomy->name ) : ?>
											<span class="description"><?php esc_html_e( 'WooCommerce already has a thumbnail for product categories. It is used as a fallback, so you only need this for a separate image.', 'taxonomy-term-images' ); ?></span>
										<?php endif; ?>
									</label><br />
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Social sharing image', 'taxonomy-term-images' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[og_image]" value="1" <?php checked( $s['og_image'] ); ?> /> <?php esc_html_e( 'Use the term image as the og:image on category, tag, and term archive pages', 'taxonomy-term-images' ); ?></label>
							<p class="description">
								<?php
								if ( Taxonomy_Term_Images::seo_plugin_active() ) {
									esc_html_e( 'An SEO plugin is active, so this is skipped. Set the sharing image in your SEO plugin instead.', 'taxonomy-term-images' );
								} else {
									esc_html_e( 'Facebook, LinkedIn, and other sites show this image when an archive page is shared. It is skipped automatically when Yoast SEO, Rank Math, SEOPress, or All in One SEO is active.', 'taxonomy-term-images' );
								}
								?>
							</p>
						</td>
					</tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Show term images', 'taxonomy-term-images' ); ?></h2>
			<ul class="ul-disc">
				<li><?php esc_html_e( 'Block: add the Term Image block to your category, tag, or taxonomy archive template in the Site Editor.', 'taxonomy-term-images' ); ?></li>
				<li><?php esc_html_e( 'Shortcode on an archive page:', 'taxonomy-term-images' ); ?> <code>[term_image size="large"]</code></li>
				<li><?php esc_html_e( 'Shortcode for a specific term:', 'taxonomy-term-images' ); ?> <code>[term_image term="news" taxonomy="category" size="medium" link="yes"]</code></li>
				<li><?php esc_html_e( 'PHP in a theme:', 'taxonomy-term-images' ); ?> <code>echo taxonomy_term_images_get_html( get_queried_object(), 'large' );</code></li>
				<li><?php esc_html_e( 'REST API: the attachment ID is in the meta field ttimg_image_id of each term.', 'taxonomy-term-images' ); ?></li>
			</ul>
			<?php self::render_support(); ?>
		</div>
		<?php
	}
}
