<?php
/**
 * Template routing.
 *
 * - Archive: a real, assignable page template ("NGCV — Articles Archive").
 *   WordPress keeps the page's ID, slug, URL and content; only presentation
 *   changes. An optional, filter-driven fallback supports verified page IDs
 *   (including the case where the Articles page is the assigned posts page).
 * - Single: posts (post type `post`) use the plugin's article view. Pages,
 *   attachments, and custom post types are never touched.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Template_Router {

	/**
	 * Internal template key registered in the "Page Attributes → Template" dropdown.
	 *
	 * @var string
	 */
	const ARCHIVE_TEMPLATE = 'ngcv-articles-archive.php';

	/**
	 * Wire routing hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'theme_page_templates', array( __CLASS__, 'register_archive_template' ) );
		add_filter( 'template_include', array( __CLASS__, 'route' ), 99 );
	}

	/**
	 * Offer the archive template in the page-template dropdown (classic themes).
	 *
	 * @param array $templates Existing templates.
	 * @return array
	 */
	public static function register_archive_template( $templates ) {
		if ( ! is_array( $templates ) ) {
			$templates = array();
		}
		if ( ! isset( $templates[ self::ARCHIVE_TEMPLATE ] ) ) {
			$templates[ self::ARCHIVE_TEMPLATE ] = __( 'NGCV — Articles Archive', 'nubian-geographic-content-views' );
		}
		return $templates;
	}

	/**
	 * Route the two supported contexts to the plugin templates.
	 *
	 * @param string $template Template WordPress resolved.
	 * @return string Absolute template path.
	 */
	public static function route( $template ) {
		if ( is_feed() || is_embed() || is_trackback() ) {
			return $template;
		}

		// Single posts → editorial article view (pages/CPTs unaffected).
		if ( is_singular( 'post' ) && apply_filters( 'ngcv_use_single_template', true ) ) {
			return ngcv_locate_template( 'single-article.php' );
		}

		// Page explicitly assigned the NGCV archive template.
		if ( is_page() && self::page_has_archive_template( get_queried_object_id() ) ) {
			return ngcv_locate_template( 'archive-articles.php' );
		}

		// Optional fallback for verified page IDs (e.g. posts-page setups).
		if ( is_home() && self::is_home_fallback() ) {
			return ngcv_locate_template( 'archive-articles.php' );
		}

		return $template;
	}

	/**
	 * Whether the given page ID carries the NGCV archive template.
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	public static function page_has_archive_template( $page_id ) {
		$page_id = absint( $page_id );
		if ( ! $page_id ) {
			return false;
		}
		return self::ARCHIVE_TEMPLATE === get_page_template_slug( $page_id );
	}

	/**
	 * The official WordPress Posts page ID (`page_for_posts` option), or 0.
	 *
	 * This is WordPress' own concept — distinct from the NGCV configured
	 * archive page and from a static page carrying the NGCV page template.
	 *
	 * @return int
	 */
	public static function posts_page_id() {
		return absint( get_option( 'page_for_posts' ) );
	}

	/**
	 * The NGCV configured archive page IDs: the plugin setting plus its
	 * Polylang translation for the current language (read-only), plus the
	 * legacy `ngcv_articles_page_ids` filter values. Never guesses.
	 *
	 * @return int[]
	 */
	public static function configured_archive_ids() {
		$ids = array();

		$setting_id = (int) NGCV_Settings::articles_page_id();
		if ( $setting_id ) {
			$ids[] = $setting_id;
			if ( NGCV_Polylang::is_active() ) {
				$translation = NGCV_Polylang::get_translation( $setting_id );
				if ( $translation ) {
					$ids[] = $translation;
				}
			}
		}

		$filtered = apply_filters( 'ngcv_articles_page_ids', array() );
		if ( ! empty( $filtered ) ) {
			foreach ( (array) $filtered as $id ) {
				$ids[] = absint( $id );
			}
		}

		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		return $ids;
	}

	/**
	 * Whether a page ID is an explicitly configured NGCV archive target
	 * (setting/translation or legacy filter).
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	public static function is_configured_archive_page( $page_id ) {
		$page_id = absint( $page_id );
		if ( ! $page_id ) {
			return false;
		}
		return in_array( $page_id, self::configured_archive_ids(), true );
	}

	/**
	 * The official WordPress Posts page (`page_for_posts`) is itself a valid
	 * NGCV archive target: when WordPress renders it, `is_page()` becomes
	 * false and `is_home()` becomes true, so the static-page template route
	 * can never match there. No page template is required to recognise the
	 * official Posts page.
	 *
	 * The explicit NGCV setting and the legacy `ngcv_articles_page_ids`
	 * filter are preserved (they select the header/header-source and remain
	 * authoritative for explicit targeting); the `ngcv_enable_posts_page_archive`
	 * filter (default true) allows opting out back to strict configured-only
	 * matching. Never guesses IDs.
	 *
	 * @return bool
	 */
	public static function is_home_fallback() {
		$posts_page = self::posts_page_id();
		if ( ! $posts_page ) {
			return false;
		}

		$enable = apply_filters( 'ngcv_enable_posts_page_archive', true );
		if ( ! $enable ) {
			return self::is_configured_archive_page( $posts_page );
		}

		return true;
	}

	/**
	 * Whether the current request renders the NGCV archive (either route).
	 *
	 * @return bool
	 */
	public static function is_archive_request() {
		if ( is_page() && self::page_has_archive_template( get_queried_object_id() ) ) {
			return true;
		}
		return is_home() && self::is_home_fallback();
	}
}
