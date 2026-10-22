=== Taxonomy Term Images – Add Images to Categories, Tags & Custom Taxonomies ===
Contributors: imalinawaz
Donate link: https://www.buymeacoffee.com/bealinawaz
Tags: category image, taxonomy image, term image, category thumbnail, custom taxonomy
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a category image, tag image, or custom taxonomy image field with the media library. Show term images with a block, shortcode, or PHP.

== Description ==

Taxonomy Term Images adds an image field to categories, tags, and custom taxonomies. Pick a category image or category thumbnail from the media library when you add or edit a term, then show it on archive pages, in category grids, or anywhere in your theme.

WordPress gives posts a featured image but gives terms none. This plugin fills that gap with a custom taxonomy image field that stores the attachment ID, so every term image is output with responsive `srcset` sizes and proper alt text.

It is useful for blog category headers, recipe or portfolio taxonomies, product brands and tags, locations, team departments, and any custom taxonomy that needs a picture.

**Why use it**

* **Works with any taxonomy:** choose which public taxonomies get the image field: categories, tags, WooCommerce product tags and brands, or your own custom taxonomies.
* **Media library picker:** select, replace, or remove the term image on the Add and Edit term screens, with a preview.
* **Thumbnail column:** see each term's image in the categories, tags, or taxonomy list.
* **Term Image block:** add it to your category or taxonomy archive template in the Site Editor to show the image of the term being viewed.
* **Shortcode:** `[term_image]` on an archive page, or `[term_image term="news" taxonomy="category" size="medium"]` anywhere.
* **Template functions:** `taxonomy_term_images_get_id()`, `taxonomy_term_images_get_url()`, and `taxonomy_term_images_get_html()` for theme developers.
* **REST API:** the image ID is available in the term's `meta` field, for headless sites and apps.
* **Social sharing image (optional):** use the term image as the `og:image` on archive pages. It is skipped automatically when Yoast SEO, Rank Math, SEOPress, or All in One SEO is active.
* **Responsive and accessible:** images use WordPress image sizes with `srcset`, and the alt text falls back to the term name.

**How it works**

1. Go to **Settings → Term Images** and choose the taxonomies that need an image. Categories and tags are on by default.
2. Edit a category, tag, or term and click **Select image**.
3. Show the image with the Term Image block, the shortcode, or a template function.

**Custom development**

Need category landing pages, custom taxonomy templates, more term fields, or a brand or product category design built into your theme? The plugin is built and maintained by Ali Nawaz, a WordPress and WooCommerce developer. [Get in touch for custom work](https://bealinawaz.com/contact-me/?utm_source=wordpress.org&utm_medium=plugin-readme&utm_campaign=taxonomy-term-images).

== Installation ==

1. Install and activate the plugin.
2. Go to **Settings → Term Images** and choose the taxonomies that get an image field.
3. Edit a term under **Posts → Categories**, **Posts → Tags**, or your custom taxonomy, select an image, and save.
4. Add the Term Image block to an archive template, or use the `[term_image]` shortcode.

== Frequently Asked Questions ==

= How do I show the category image on the category archive page? =

In a block theme, open **Appearance → Editor → Templates**, edit the Category (or Archive) template, and add the **Term Image** block. In a classic theme, add `<?php echo taxonomy_term_images_get_html( get_queried_object(), 'large' ); ?>` to `category.php` or `archive.php`. You can also use the `[term_image]` shortcode where shortcodes run.

= How do I show the image of a specific category or term? =

Use `[term_image term="news" taxonomy="category" size="medium"]`. The `term` can be a slug or an ID. Add `link="yes"` to link the image to the term archive, and `class="my-class"` for extra CSS classes.

= Can I add images to a custom taxonomy? =

Yes. Every public taxonomy with an admin screen is listed under Settings → Term Images. Check the ones you want. Developers can also use the `taxonomy_term_images_taxonomies` filter.

= Does it work with WooCommerce product categories? =

WooCommerce already has a thumbnail field for product categories, so you usually do not need this plugin for them. If you turn it on for product categories, the WooCommerce thumbnail is used as a fallback. It is most useful for product tags, brands, and attribute-like custom taxonomies.

= How do I get the term image in the REST API? =

Each enabled term has a `meta.ttimg_image_id` field with the attachment ID, for example in `/wp-json/wp/v2/categories/5`. Users who can edit the term can update it through the API.

= Will it add a duplicate og:image when I use an SEO plugin? =

No. The social sharing image is off by default, and when it is on it is skipped if Yoast SEO, Rank Math, SEOPress, or All in One SEO is active.

= What happens to the images if I uninstall the plugin? =

The images stay in your media library. The plugin removes its settings and the links between terms and images.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
