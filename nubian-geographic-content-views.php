<?php
/**
 * Plugin Name:       Nubian Geographic – Content Views
 * Description:       Presentation layer for the Nubian Geographic articles archive and single articles. Presentation only — it does not create, modify, or migrate any content, URLs, taxonomies, translations, or SEO metadata.
 * Version:           1.1.1
 * Requires at least: 5.9
 * Requires PHP:      7.2
 * Author:            Nubian Geographic
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nubian-geographic-content-views
 * Domain Path:       /languages
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

define( 'NGCV_VERSION', '1.1.1' );
define( 'NGCV_PLUGIN_FILE', __FILE__ );
define( 'NGCV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NGCV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NGCV_TEMPLATE_DIR', NGCV_PLUGIN_DIR . 'templates/' );

require_once NGCV_PLUGIN_DIR . 'includes/class-polylang.php';
require_once NGCV_PLUGIN_DIR . 'includes/class-archive.php';
require_once NGCV_PLUGIN_DIR . 'includes/class-single.php';
require_once NGCV_PLUGIN_DIR . 'includes/class-assets.php';
require_once NGCV_PLUGIN_DIR . 'includes/class-template-router.php';
require_once NGCV_PLUGIN_DIR . 'includes/class-plugin.php';

NGCV_Plugin::instance();

/**
 * Locate a plugin template, allowing a child-theme override in
 * <child-theme>/ngcv-templates/<file>. Returns an absolute path or ''.
 *
 * @param string $relative Relative template path, e.g. 'parts/article-card.php'.
 * @return string
 */
function ngcv_locate_template( $relative ) {
	$relative = ltrim( (string) $relative, '/' );
	$theme    = trailingslashit( get_stylesheet_directory() ) . 'ngcv-templates/' . $relative;
	$default  = NGCV_TEMPLATE_DIR . $relative;

	$path = file_exists( $theme ) ? $theme : $default;
	return apply_filters( 'ngcv_template_path', $path, $relative );
}

/**
 * Render a plugin template part with optional variables.
 *
 * @param string $relative Relative template path.
 * @param array  $vars     Variables to expose to the template.
 * @return void
 */
function ngcv_get_template( $relative, $vars = array() ) {
	$file = ngcv_locate_template( $relative );
	if ( ! $file || ! file_exists( $file ) ) {
		return;
	}
	if ( ! empty( $vars ) && is_array( $vars ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- intentional, scoped template vars.
		extract( $vars, EXTR_SKIP );
	}
	include $file;
}
