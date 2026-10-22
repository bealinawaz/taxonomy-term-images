# Taxonomy Term Images

Add an image field to categories, tags, and custom taxonomies in WordPress. Pick the image from the media library on the term screens, then show it with a block, a shortcode, template functions, or the REST API. Images are stored as attachment IDs, so they render with responsive `srcset` and alt text.

![Tested up to WordPress 7.1](https://img.shields.io/badge/WordPress-7.1-blue)
![PHP 7.4 to 8.5](https://img.shields.io/badge/PHP-7.4%E2%80%938.5-777bb4)
![License GPLv2 or later](https://img.shields.io/badge/license-GPLv2%2B-green)

## Features

- **Any public taxonomy:** choose which ones get the field under **Settings → Term Images** (categories and tags by default).
- **Media library picker** on the Add and Edit term screens, with preview, replace, and remove.
- **Thumbnail column** in the term list tables.
- **Term Image block** for archive templates (dynamic, no build step).
- **Shortcode** `[term_image]` for the current archive term or any term.
- **Template functions** for themes.
- **REST API:** `meta.ttimg_image_id` on each enabled term, editable by users who can edit the term.
- **Optional og:image** on term archives, skipped when Yoast SEO, Rank Math, SEOPress, or All in One SEO is active.
- **WooCommerce aware:** product category thumbnails are used as a fallback.

## Usage

### Shortcode

```
[term_image]                                             Current archive term
[term_image term="news" taxonomy="category" size="medium"]
[term_image term="12" size="large" link="yes" class="rounded"]
```

`[taxonomy_term_image]` is an alias, registered always (`[term_image]` is skipped if another plugin already uses that name).

### Template functions

```php
// Attachment ID (int, 0 when none). $term: WP_Term, term ID, or null for the current archive term.
$id = taxonomy_term_images_get_id( $term );

// Image URL for a size, or ''.
$url = taxonomy_term_images_get_url( $term, 'medium' );

// <img> with srcset, sizes, and alt (falls back to the term name), or ''.
echo taxonomy_term_images_get_html( $term, 'large', array( 'class' => 'term-hero' ) );
```

Category grid example:

```php
foreach ( get_terms( array( 'taxonomy' => 'category' ) ) as $term ) {
	printf(
		'<a href="%s">%s<span>%s</span></a>',
		esc_url( get_term_link( $term ) ),
		taxonomy_term_images_get_html( $term, 'medium' ),
		esc_html( $term->name )
	);
}
```

### REST API

```
GET /wp-json/wp/v2/categories/5
{ ..., "meta": { "ttimg_image_id": 42 } }
```

## Hooks

- `taxonomy_term_images_taxonomies` (filter): taxonomy names that get the image field.
- `taxonomy_term_images_image_id` (filter): change the attachment ID returned for a term.
- `taxonomy_term_images_seo_plugin_active` (filter): return true to turn off the og:image output.

## Project structure

```
taxonomy-term-images.php                         Plugin header and bootstrap
includes/class-taxonomy-term-images.php          Term meta, shortcode, block, og:image
includes/class-taxonomy-term-images-settings.php Settings defaults and validation
includes/class-taxonomy-term-images-admin.php    Term form fields, list column, settings page
includes/template-functions.php                  Public template functions
assets/admin.js, admin.css                       Media library picker and admin styles
assets/block/                                    Term Image block (block.json and editor script, no build step)
uninstall.php                                    Removes settings and term image meta
readme.txt                                       WordPress.org readme
```

## Custom development

Need category landing pages, custom taxonomy templates, more term fields, or a brand or product category design built into your theme? The plugin is built and maintained by [Ali Nawaz](https://bealinawaz.com/?utm_source=github&utm_medium=readme&utm_campaign=taxonomy-term-images), a WordPress and WooCommerce developer. [Get in touch for custom work](https://bealinawaz.com/contact-me/?utm_source=github&utm_medium=readme&utm_campaign=taxonomy-term-images).

## License

GPLv2 or later. See [LICENSE](LICENSE).
