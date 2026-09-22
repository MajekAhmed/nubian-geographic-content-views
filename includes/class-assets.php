<?php
/**
 * Conditional asset loading and image sizes.
 *
 * CSS/JS are enqueued ONLY inside the two routed contexts (articles archive,
 * single article). Nothing is loaded globally. No webfonts are added: the
 * plugin inherits the typography configured in the theme customizer.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Assets {

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_image_sizes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		// Fonts are enqueued later so the theme (which loads its local webfont
		// downloader inside its own wp_enqueue_scripts callback at priority 10)
		// has already registered wptt_get_webfont_url() when we look for it.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_fonts' ), 20 );
		add_filter( 'wp_resource_hints', array( __CLASS__, 'resource_hints' ), 10, 2 );
	}

	/**
	 * Cached font stylesheet URL.
	 *
	 * @var string|null
	 */
	private static $fonts_url = null;

	/**
	 * Responsive image sizes used by the plugin's views.
	 *
	 * ngcv-card  — landscape card (8:5), matches the standard grid card box.
	 * ngcv-card-tall — portrait card (4:5) used by the grid's tall variant.
	 * ngcv-hero  — wide documentary image (16:9) for features and article plates.
	 *
	 * Note: WordPress only generates a size for images uploaded after it is
	 * registered; older media falls back to the closest available size (the
	 * browser still picks the best candidate from srcset). Running a thumbnail
	 * regeneration is recommended once after deployment.
	 *
	 * @return void
	 */
	public static function register_image_sizes() {
		add_image_size( 'ngcv-card', 768, 480, true );
		add_image_size( 'ngcv-card-tall', 720, 900, true );
		add_image_size( 'ngcv-hero', 1600, 900, true );
	}

	/**
	 * Sizes attribute for a given view context, so browsers download an
	 * appropriately sized candidate instead of the largest one.
	 *
	 * @param string $context 'card', 'tall' or 'lead'.
	 * @return string
	 */
	public static function image_sizes_attr( $context = 'card' ) {
		switch ( $context ) {
			case 'lead':
				return '(max-width: 767px) 100vw, (max-width: 1023px) 100vw, 66vw';
			case 'tall':
				return '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 33vw';
			case 'hero':
				return '(max-width: 767px) 100vw, (max-width: 1023px) 100vw, 66vw';
			case 'card':
			default:
				return '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 33vw';
		}
	}

	/**
	 * Brand webfont stylesheet (Inter for English, Cairo for Arabic).
	 *
	 * Everything is filterable and skippable:
	 *  - `ngcv_webfonts_enabled` (bool, default true)
	 *  - `ngcv_webfont_families` (array of Google Fonts family strings)
	 *  - `ngcv_webfont_url` (final URL)
	 *
	 * When the active theme ships the standard wptt webfont loader (Digital
	 * Newspaper does), the fonts are downloaded once and served from the site's
	 * own /wp-content/fonts — no third-party request at page-view time. If that
	 * helper is unavailable the Google Fonts stylesheet URL is used directly,
	 * and a preconnect hint is added by resource_hints().
	 *
	 * @return string
	 */
	public static function fonts_url() {
		if ( null !== self::$fonts_url ) {
			return self::$fonts_url;
		}

		$families = apply_filters(
			'ngcv_webfont_families',
			array(
				'Inter:400,500,600,700',
				'Cairo:400,600,700',
			)
		);

		if ( empty( $families ) || ! is_array( $families ) ) {
			self::$fonts_url = '';
			return self::$fonts_url;
		}

		$url = 'https://fonts.googleapis.com/css?family='
			. rawurlencode( implode( '|', array_map( 'strval', $families ) ) )
			. '&display=swap';

		if ( function_exists( 'wptt_get_webfont_url' ) ) {
			$local = wptt_get_webfont_url( $url );
			if ( is_string( $local ) && '' !== $local ) {
				$url = $local;
			}
		}

		self::$fonts_url = apply_filters( 'ngcv_webfont_url', $url );
		return self::$fonts_url;
	}

	/**
	 * Whether the font stylesheet is loaded from a third party (only true when
	 * the local webfont loader is unavailable).
	 *
	 * @return bool
	 */
	private static function fonts_are_remote() {
		return ! function_exists( 'wptt_get_webfont_url' );
	}

	/**
	 * Enqueue the brand webfonts for the routed contexts only.
	 *
	 * @return void
	 */
	public static function enqueue_fonts() {
		if ( ! NGCV_Plugin::get_context() ) {
			return;
		}
		if ( ! apply_filters( 'ngcv_webfonts_enabled', true ) ) {
			return;
		}

		$url = self::fonts_url();
		if ( '' === $url ) {
			return;
		}

		wp_enqueue_style( 'ngcv-fonts', $url, array(), null );
	}

	/**
	 * Open the connection to the font host early when the stylesheet is remote.
	 *
	 * @param array  $urls          Hint URLs.
	 * @param string $relation_type Relation type.
	 * @return array
	 */
	public static function resource_hints( $urls, $relation_type ) {
		if ( 'preconnect' !== $relation_type || ! is_array( $urls ) ) {
			return $urls;
		}
		if ( ! NGCV_Plugin::get_context() ) {
			return $urls;
		}
		if ( ! apply_filters( 'ngcv_webfonts_enabled', true ) || ! self::fonts_are_remote() ) {
			return $urls;
		}

		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);

		return $urls;
	}

	/**
	 * Optional opt-out hook: restore the theme customizer's typography inside
	 * the plugin views. Off by default (the brand specifies Inter + Cairo).
	 *
	 * @return void
	 */
	public static function maybe_apply_theme_typography() {
		if ( ! apply_filters( 'ngcv_inherit_theme_typography', false ) ) {
			return;
		}

		$css = 'body.digital_newspaper_font_typography .ngcv-context{'
			. '--ngcv-font-base:var(--content-family,var(--ngcv-font-latin));'
			. '--ngcv-font-heading:var(--single-title-family,var(--ngcv-font-latin));'
			. '--ngcv-font-card-title:var(--post-title-family,var(--ngcv-font-latin));'
			. '--ngcv-font-content:var(--single-content-family,var(--ngcv-font-latin));'
			. '--ngcv-font-meta:var(--single-meta-family,var(--ngcv-font-latin));'
			. '}';

		wp_add_inline_style( 'ngcv-tokens', $css );
	}

	/**
	 * Cache-busting version: plugin version + file modification time.
	 *
	 * @param string $relative Path relative to the plugin root.
	 * @return string
	 */
	private static function version( $relative ) {
		$version = NGCV_VERSION;
		$path    = NGCV_PLUGIN_DIR . ltrim( $relative, '/' );
		if ( file_exists( $path ) ) {
			$mtime = filemtime( $path );
			if ( $mtime ) {
				$version .= '.' . (string) $mtime;
			}
		}
		return $version;
	}

	/**
	 * Enqueue context-scoped assets.
	 *
	 * @return void
	 */
	public static function enqueue() {
		$context = NGCV_Plugin::get_context();
		if ( ! $context ) {
			return;
		}

		$css_url = NGCV_PLUGIN_URL . 'assets/css/';

		wp_enqueue_style(
			'ngcv-tokens',
			$css_url . 'tokens.css',
			array(),
			self::version( 'assets/css/tokens.css' )
		);

		if ( NGCV_Plugin::CONTEXT_ARCHIVE === $context ) {
			wp_enqueue_style(
				'ngcv-archive',
				$css_url . 'archive.css',
				array( 'ngcv-tokens' ),
				self::version( 'assets/css/archive.css' )
			);
		}

		if ( NGCV_Plugin::CONTEXT_SINGLE === $context ) {
			wp_enqueue_style(
				'ngcv-single',
				$css_url . 'single.css',
				array( 'ngcv-tokens' ),
				self::version( 'assets/css/single.css' )
			);
		}

		wp_enqueue_style(
			'ngcv-responsive',
			$css_url . 'responsive.css',
			array( 'ngcv-tokens' ),
			self::version( 'assets/css/responsive.css' )
		);

		if ( NGCV_Plugin::CONTEXT_SINGLE === $context ) {
			// Vanilla JS enhancement only (TOC toggle + scroll-spy). The
			// article body and TOC are fully server-rendered without it.
			wp_enqueue_script(
				'ngcv-content-views',
				NGCV_PLUGIN_URL . 'assets/js/content-views.js',
				array(),
				self::version( 'assets/js/content-views.js' ),
				true
			);
		}

		// Optional: hand typography back to the theme customizer.
		self::maybe_apply_theme_typography();
	}
}
