<?php
/**
 * Single-article helpers.
 *
 * The article body is rendered through the full, native WordPress content
 * pipeline (`apply_filters( 'the_content', … )` — Gutenberg blocks, shortcodes,
 * galleries, embeds, and every registered `the_content` filter are preserved).
 * Nothing is stored: the optional heading-ID injection is render-time only.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Single {

	/**
	 * Headings collected during the last content render.
	 *
	 * @var array[]
	 */
	private static $toc_items = array();

	/**
	 * Processed article content (HTML).
	 *
	 * @var string
	 */
	private static $content_html = '';

	/**
	 * Run the full content pipeline once, collecting heading data for the TOC.
	 * Must be called inside the Loop.
	 *
	 * @return void
	 */
	public static function prepare_content() {
		self::$toc_items   = array();
		self::$content_html = '';

		add_filter( 'the_content', array( __CLASS__, 'inject_heading_ids' ), 99998 );
		$content = get_the_content();
		$content = apply_filters( 'the_content', $content );
		remove_filter( 'the_content', array( __CLASS__, 'inject_heading_ids' ), 99998 );

		self::$content_html = $content;
	}

	/**
	 * Echo the processed article content. Equivalent to the_content() output,
	 * with optional stable heading IDs injected at render time only.
	 *
	 * @return void
	 */
	public static function print_content() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- processed post content, same trust level as the_content().
		echo self::$content_html;
	}

	/**
	 * Render-time injection of stable IDs into h2/h3 headings so the TOC can
	 * deep-link. Existing IDs (e.g. from another TOC plugin) are reused, never
	 * duplicated. Nothing is persisted to the database.
	 *
	 * @param string $content Content after all other filters have run.
	 * @return string
	 */
	public static function inject_heading_ids( $content ) {
		if ( '' === trim( (string) $content ) ) {
			return $content;
		}

		$items = array();
		$used  = array();

		$callback = function ( $matches ) use ( &$items, &$used ) {
			$level = (int) $matches[1];
			$attrs = $matches[2];
			$inner = $matches[3];

			$id = '';
			if ( preg_match( '/\bid\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $attrs, $attr_match ) ) {
				$id = ( '' !== $attr_match[1] ) ? $attr_match[1] : $attr_match[2];
			}

			$text = trim( wp_strip_all_tags( $inner ) );

			if ( '' === $id ) {
				$slug = $text ? sanitize_title( $text ) : '';
				if ( '' === $slug ) {
					$slug = 'section';
				}
				if ( strlen( $slug ) > 64 ) {
					$slug = substr( $slug, 0, 64 );
				}
				$base = $slug;
				$suffix = 1;
				while ( isset( $used[ 'ngcv-' . $slug ] ) ) {
					$suffix++;
					$slug = $base . '-' . $suffix;
				}
				$id     = 'ngcv-' . $slug;
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$used[ $id ] = true;
			if ( '' !== $text && count( $items ) < 60 ) {
				$items[] = array(
					'level' => $level,
					'id'    => $id,
					'text'  => $text,
				);
			}

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		};

		$content = preg_replace_callback( '/<h([23])([^>]*)>(.*?)<\/h\1>/is', $callback, $content );

		self::$toc_items = $items;
		return $content;
	}

	/**
	 * Heading items collected during prepare_content().
	 *
	 * @return array[]
	 */
	public static function toc_items() {
		return self::$toc_items;
	}

	/**
	 * Whether the TOC should render for the current article.
	 *
	 * @return bool
	 */
	public static function should_render_toc() {
		if ( ! apply_filters( 'ngcv_enable_toc', true ) ) {
			return false;
		}
		$min = (int) apply_filters( 'ngcv_toc_min_items', 3 );
		$min = $min > 0 ? $min : 3;
		return count( self::$toc_items ) >= $min;
	}

	/**
	 * Unicode-aware word count of the article body (works for Arabic and
	 * English, unlike the theme's str_word_count-based estimate).
	 *
	 * @param int $post_id Post ID (defaults to the current post).
	 * @return int Number of letter/number runs, or 0 when there is none.
	 */
	public static function word_count( $post_id = 0 ) {
		$post_id = $post_id ? absint( $post_id ) : (int) get_the_ID();
		if ( ! $post_id ) {
			return 0;
		}

		$content = (string) get_post_field( 'post_content', $post_id );
		$plain   = trim( wp_strip_all_tags( $content ) );
		if ( '' === $plain ) {
			return 0;
		}

		$count = 0;
		if ( preg_match_all( '/[\p{L}\p{N}]+/u', $plain, $matches ) ) {
			$count = count( $matches[0] );
		}

		return max( 0, (int) $count );
	}

	/**
	 * Unicode-aware reading time in whole minutes.
	 *
	 * @param int $post_id Post ID.
	 * @return int Minutes (0 when there is no readable content).
	 */
	public static function reading_time( $post_id = 0 ) {
		$count = self::word_count( $post_id );
		if ( $count < 1 ) {
			return 0;
		}

		$wpm     = (int) apply_filters( 'ngcv_reading_time_wpm', 180 );
		$wpm     = $wpm > 60 ? $wpm : 60;
		$minutes = (int) ceil( $count / $wpm );

		return max( 1, $minutes );
	}

	/**
	 * Whether the post has a manually written excerpt (used as the editorial
	 * standfirst). Never fabricates one from the body.
	 *
	 * @return bool
	 */
	public static function has_manual_excerpt() {
		$excerpt = (string) get_post_field( 'post_excerpt', get_the_ID() );
		return '' !== trim( $excerpt );
	}

	/**
	 * Related-articles query: existing categories first, tags as fallback,
	 * current post excluded, limited, language-scoped. No new taxonomies and
	 * no artificial relationships are created.
	 *
	 * @return WP_Query|null
	 */
	public static function related_query() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return null;
		}

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		$category_ids = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
		if ( ! empty( $category_ids ) ) {
			$args['category__in'] = array_map( 'absint', $category_ids );
		} else {
			$tag_ids = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
			if ( ! empty( $tag_ids ) ) {
				$args['tag__in'] = array_map( 'absint', $tag_ids );
			}
		}

		$lang = NGCV_Polylang::query_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}

		$args   = apply_filters( 'ngcv_related_query_args', $args, $post_id );
		$query  = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return null;
		}
		return $query;
	}

	/**
	 * Previous/next post navigation with translated, arrow-free labels
	 * (direction-neutral, RTL-safe).
	 *
	 * @return void
	 */
	public static function post_navigation() {
		the_post_navigation(
			array(
				'prev_text'          => '<span class="ngcv-nav-label">' . esc_html__( 'Previous', 'nubian-geographic-content-views' ) . '</span><span class="ngcv-nav-title">%title</span>',
				'next_text'          => '<span class="ngcv-nav-label">' . esc_html__( 'Next', 'nubian-geographic-content-views' ) . '</span><span class="ngcv-nav-title">%title</span>',
				'screen_reader_text' => esc_html__( 'Post navigation', 'nubian-geographic-content-views' ),
			)
		);
	}
}

