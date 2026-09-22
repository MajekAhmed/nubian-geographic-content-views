<?php
/**
 * Read-only Polylang adapter.
 *
 * Every call is guarded with function_exists so the site keeps working if
 * Polylang is deactivated or updated. This class NEVER writes translations,
 * never sets languages, and never creates language records — it only reads
 * the existing language relationships that Polylang owns.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Polylang {

	/**
	 * Whether Polylang (or a compatible API) is available.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return function_exists( 'pll_current_language' )
			&& function_exists( 'pll_is_translated_post_type' )
			&& function_exists( 'pll_get_post' );
	}

	/**
	 * Current language slug (e.g. 'ar', 'en') or '' when unknown.
	 *
	 * @param string $field 'slug' | 'locale' | 'name'.
	 * @return string
	 */
	public static function current_language( $field = 'slug' ) {
		if ( ! self::is_active() ) {
			return '';
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Polylang public API, guarded.
		$value = pll_current_language( $field );
		return is_string( $value ) ? $value : '';
	}

	/**
	 * Whether Polylang manages languages/translations for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function manages_post_type( $post_type = 'post' ) {
		if ( ! self::is_active() ) {
			return false;
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Polylang public API, guarded.
		return (bool) pll_is_translated_post_type( $post_type );
	}

	/**
	 * ID of the translation of a post in a given language, or 0 when none.
	 * Read-only: never creates a translation.
	 *
	 * @param int    $post_id Post/page ID.
	 * @param string $slug    Language slug ('' = current language).
	 * @return int
	 */
	public static function get_translation( $post_id, $slug = '' ) {
		$post_id = absint( $post_id );
		if ( ! self::is_active() || ! $post_id ) {
			return 0;
		}
		$slug = $slug ? $slug : self::current_language();
		if ( ! $slug ) {
			return 0;
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Polylang public API, guarded.
		$translation = pll_get_post( $post_id, $slug );
		return $translation ? absint( $translation ) : 0;
	}

	/**
	 * Language switcher data for the current context.
	 *
	 * Returns only languages that actually have a translation (no broken
	 * links). If fewer than two usable languages remain, the switcher is
	 * suppressed entirely (nothing useful to show).
	 *
	 * @param int $post_id When set, links point to translations of this post/page.
	 * @return array[] Each: name, slug, url, current (bool).
	 */
	public static function language_switcher( $post_id = 0 ) {
		if ( ! self::is_active() || ! function_exists( 'pll_the_languages' ) ) {
			return array();
		}

		$args = array( 'raw' => 1, 'echo' => 0 );
		if ( $post_id ) {
			$args['post_id'] = absint( $post_id );
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Polylang public API, guarded.
		$langs = pll_the_languages( $args );
		if ( ! is_array( $langs ) ) {
			return array();
		}

		$items = array();
		foreach ( $langs as $lang ) {
			if ( ! is_array( $lang ) ) {
				continue;
			}
			if ( ! empty( $lang['no_translation'] ) ) {
				continue; // Never render a link to a translation that does not exist.
			}
			$items[] = array(
				'name'    => isset( $lang['name'] ) ? (string) $lang['name'] : '',
				'slug'    => isset( $lang['slug'] ) ? (string) $lang['slug'] : '',
				'url'     => isset( $lang['url'] ) ? (string) $lang['url'] : '',
				'current' => ! empty( $lang['current_lang'] ),
			);
		}

		if ( count( $items ) < 2 ) {
			return array();
		}
		return $items;
	}

	/**
	 * The other-language counterpart(s) of a post/page, for the compact
	 * language link in the article header. Only real translations are
	 * returned — never a link to a post that does not exist.
	 *
	 * @param int $post_id Post/page ID (0 = current context).
	 * @return array[] Each: name, slug, url, current (bool).
	 */
	public static function counterpart_link( $post_id = 0 ) {
		if ( ! self::is_active() ) {
			return array();
		}

		$post_id = $post_id ? absint( $post_id ) : 0;
		$langs   = self::language_switcher( $post_id );
		if ( empty( $langs ) ) {
			return array();
		}

		$others = array();
		foreach ( $langs as $lang ) {
			if ( empty( $lang['current'] ) && '' !== $lang['url'] ) {
				$others[] = $lang;
			}
		}

		return apply_filters( 'ngcv_counterpart_links', $others, $post_id );
	}

	/**
	 * Language argument for post queries ('lang' => slug), or '' when not applicable.
	 * Polylang also auto-filters queries; passing it explicitly keeps behavior
	 * deterministic for secondary queries.
	 *
	 * @return string
	 */
	public static function query_language() {
		if ( ! self::manages_post_type( 'post' ) ) {
			return '';
		}
		return self::current_language();
	}
}
