<?php
/**
 * Core orchestrator: context detection, body classes, theme-native page shell.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Plugin {

	const CONTEXT_SINGLE  = 'single';
	const CONTEXT_ARCHIVE = 'archive';

	/**
	 * Singleton instance.
	 *
	 * @var NGCV_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Boot the plugin.
	 *
	 * @return NGCV_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire hooks. The plugin registers no cron jobs, REST routes, post-save
	 * hooks, or admin pages — it is purely a front-end presentation layer.
	 */
	private function __construct() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ), 10, 1 );

		NGCV_Template_Router::init();
		NGCV_Assets::init();
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public static function load_textdomain() {
		load_plugin_textdomain(
			'nubian-geographic-content-views',
			false,
			dirname( plugin_basename( NGCV_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Detect the current NGCV presentation context.
	 *
	 * @return string|null 'single', 'archive', or null when the plugin is not active.
	 */
	public static function get_context() {
		if ( is_admin() || is_feed() || is_embed() || is_trackback() ) {
			return null;
		}

		if ( is_singular( 'post' ) && apply_filters( 'ngcv_use_single_template', true ) ) {
			return self::CONTEXT_SINGLE;
		}

		if ( NGCV_Template_Router::is_archive_request() ) {
			return self::CONTEXT_ARCHIVE;
		}

		return null;
	}

	/**
	 * Add scoping classes to <body> for the routed contexts only.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public static function body_classes( $classes ) {
		$context = self::get_context();
		if ( ! $context ) {
			return $classes;
		}

		$classes[] = 'ngcv-active';
		$classes[] = 'ngcv-' . $context;

		$lang = NGCV_Polylang::current_language();
		if ( $lang ) {
			$safe = sanitize_html_class( $lang );
			if ( $safe ) {
				$classes[] = 'ngcv-lang-' . $safe;
			}
		}

		return $classes;
	}

	/**
	 * Open the same structural shell the active theme uses around its content
	 * templates, so header/footer, menus, sidebars, width layout, and any theme
	 * hook behave exactly as on native templates.
	 *
	 * @param string $context 'single' or 'archive'.
	 * @return void
	 */
	public static function shell_open( $context ) {
		$width = function_exists( 'digial_newspaper_get_section_width_layout_val' )
			? digial_newspaper_get_section_width_layout_val()
			: 'boxed';

		echo '<div id="theme-content" class="ngcv-context ngcv-context-' . esc_attr( $context ) . '">';

		if ( has_action( 'digital_newspaper_before_main_content' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- theme hook, reproduced for parity.
			do_action( 'digital_newspaper_before_main_content' );
		}

		echo '<main id="primary" class="site-main width-' . esc_attr( $width ) . '">';
		echo '<div class="digital-newspaper-container"><div class="row">';
		echo '<div class="secondary-left-sidebar">';
		get_sidebar( 'left' );
		echo '</div><div class="primary-content">';

		// The theme renders its breadcrumb (and any of its own extensions) here,
		// exactly as on its native templates — one breadcrumb, theme-managed.
		if ( has_action( 'digital_newspaper_before_inner_content' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- theme hook, reproduced for parity.
			do_action( 'digital_newspaper_before_inner_content' );
		}
	}

	/**
	 * Close the theme-native shell opened by shell_open().
	 *
	 * @param string $context Unused context (kept for symmetry/overrides).
	 * @return void
	 */
	public static function shell_close( $context = '' ) {
		unset( $context );

		echo '</div><div class="secondary-sidebar">';
		get_sidebar();
		echo '</div></div></div></main></div>';
	}
}
