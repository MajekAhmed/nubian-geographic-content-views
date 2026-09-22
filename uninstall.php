<?php
/**
 * Uninstall routine for Nubian Geographic – Content Views.
 *
 * SAFETY: The plugin persists no options, transients, post meta, post content,
 * taxonomy terms, translations, SEO metadata, or user data. It therefore has
 * nothing to remove from the database. Content (posts, pages, categories, tags,
 * media, Polylang relationships, Rank Math metadata) is intentionally NOT
 * touched on uninstall.
 *
 * The page-template assignment stored by WordPress on the Articles page
 * (post meta `_wp_page_template`) belongs to WordPress' own page-attachment
 * system and is left in place; if the plugin is gone, WordPress simply falls
 * back to the theme's default page template.
 *
 * @package nubian-geographic-content-views
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Intentionally no database operations. The plugin is stateless by design.
