<?php
/**
 * Articles archive data layer.
 *
 * The archive renders the Articles page (title, existing page content as the
 * editorial introduction, category navigation) plus a secondary, paged post
 * query. All content shown already exists; nothing is invented. Queries are
 * language-scoped through the read-only Polylang adapter.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Archive {

	/**
	 * Secondary query cache (page mode only).
	 *
	 * @var WP_Query|null
	 */
	private static $query = null;

	/**
	 * Page header cache.
	 *
	 * @var array|null
	 */
	private static $header = null;

	/**
	 * 'home' when the Articles page is the assigned posts page (main loop
	 * contains the posts); 'page' for a normal static page (secondary query).
	 *
	 * @return string
	 */
	public static function mode() {
		return NGCV_Template_Router::is_home_fallback() ? 'home' : 'page';
	}

	/**
	 * The posts query for the grid.
	 *
	 * Page mode: a fresh, paged, language-scoped WP_Query.
	 * Home mode: the main query (already paged by WordPress).
	 *
	 * @return WP_Query
	 */
	public static function posts_query() {
		if ( 'home' === self::mode() ) {
			global $wp_query;
			return $wp_query;
		}

		if ( null !== self::$query ) {
			return self::$query;
		}

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => (int) get_option( 'posts_per_page' ),
			'paged'               => max( 1, (int) get_query_var( 'paged' ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false, // Needed for native pagination.
			'orderby'             => 'date',
			'order'               => 'DESC',
		);

		$lang = NGCV_Polylang::query_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}

		$args        = apply_filters( 'ngcv_archive_query_args', $args );
		self::$query = new WP_Query( $args );
		return self::$query;
	}

	/**
	 * Whether the grid has any posts.
	 *
	 * @return bool
	 */
	public static function has_posts() {
		$query = self::posts_query();
		return $query && $query->have_posts();
	}

	/**
	 * Total number of published articles visible in the current archive query
	 * (language-scoped by Polylang). Used for the masthead count line.
	 *
	 * @return int
	 */
	public static function found_posts() {
		$query = self::posts_query();
		return $query ? (int) $query->found_posts : 0;
	}

	/**
	 * Whether the featured story should be rendered: first page of the archive
	 * only, and only when there is at least one more post for the grid to hold.
	 * The featured entry is the first post of the existing query — no extra
	 * query, no invented content, and the grid is never left empty.
	 *
	 * @return bool
	 */
	public static function has_feature() {
		if ( ! apply_filters( 'ngcv_show_featured_story', true ) ) {
			return false;
		}

		if ( max( 1, (int) get_query_var( 'paged' ) ) > 1 ) {
			return false;
		}

		$query = self::posts_query();
		return $query && (int) $query->post_count > 1;
	}

	/**
	 * Render the featured story (the first post of the current query page).
	 *
	 * @return void
	 */
	public static function feature() {
		if ( ! self::has_feature() ) {
			return;
		}

		$query = self::posts_query();
		if ( ! $query ) {
			return;
		}

		$query->rewind_posts();
		if ( ! $query->have_posts() ) {
			return;
		}

		$query->the_post();
		ngcv_get_template( 'parts/featured-article.php' );
		wp_reset_postdata();
	}

	/**
	 * Render the article cards for the current page of results.
	 *
	 * Cards carry an editorial rhythm class so the grid is not a row of
	 * identical tiles: the opening card of a grid page (when no featured story
	 * occupies it) becomes a wide panel, and every fifth card becomes a taller,
	 * portrait-format entry.
	 *
	 * @return void
	 */
	public static function grid() {
		$query = self::posts_query();
		if ( ! $query ) {
			return;
		}

		$query->rewind_posts();

		$feature = self::has_feature();
		$skip    = $feature ? 1 : 0;
		$index   = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			$index++;

			if ( $index <= $skip ) {
				continue; // Already rendered as the featured story.
			}

			// Cards sit inside the labelled grid <section> (h2), so they are
			// always h3 — one heading level below their section heading.
			ngcv_get_template(
				'parts/article-card.php',
				array(
					'variant'       => self::card_variant( $index - $skip ),
					'heading_level' => 3,
				)
			);
		}
		wp_reset_postdata();
	}

	/**
	 * Editorial variant for a card at a given position in the grid.
	 *
	 * @param int $position 1-based position within the grid.
	 * @return string 'lead' | 'tall' | 'standard'
	 */
	public static function card_variant( $position ) {
		$position = (int) $position;
		$variant  = 'standard';

		if ( 1 === $position && ! self::has_feature() && apply_filters( 'ngcv_archive_lead_card', true ) ) {
			$variant = 'lead';
		} elseif ( $position > 1 && 1 === ( $position % 5 ) && apply_filters( 'ngcv_archive_tall_card', true ) ) {
			$variant = 'tall';
		}

		return apply_filters( 'ngcv_archive_card_variant', $variant, $position );
	}

	/**
	 * Header data: page title, existing page content (introduction), page ID.
	 * Runs/rewinds the main loop in page mode; reads the posts-page settings
	 * in home mode.
	 *
	 * @return array{title:string, intro_html:string, id:int}
	 */
	public static function header() {
		if ( null !== self::$header ) {
			return self::$header;
		}

		$data = array(
			'title'      => '',
			'intro_html' => '',
			'id'         => 0,
		);

		if ( 'home' === self::mode() ) {
			$page_id    = absint( get_option( 'page_for_posts' ) );
			$data['id'] = $page_id;
			if ( $page_id ) {
				$data['title'] = get_the_title( $page_id );
				$content       = (string) get_post_field( 'post_content', $page_id );
				if ( '' !== trim( $content ) ) {
					$data['intro_html'] = apply_filters( 'the_content', $content );
				}
			}
			if ( '' === $data['title'] ) {
				$data['title'] = __( 'Articles', 'nubian-geographic-content-views' );
			}
		} elseif ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$data['id']    = get_the_ID();
				$data['title'] = get_the_title();
				$content       = get_the_content();
				if ( '' !== trim( (string) $content ) ) {
					// Full WordPress pipeline — blocks, shortcodes, embeds preserved.
					$data['intro_html'] = apply_filters( 'the_content', $content );
				}
			}
			rewind_posts();
		}

		self::$header = $data;
		return $data;
	}

	/**
	 * Page title for the archive <h1>.
	 *
	 * @return string
	 */
	public static function page_title() {
		$header = self::header();
		return $header['title'];
	}

	/**
	 * Processed page content (the editorial introduction), if any.
	 *
	 * @return string
	 */
	public static function intro_html() {
		$header = self::header();
		return $header['intro_html'];
	}

	/**
	 * Current Articles page ID (for the language switcher, etc.).
	 *
	 * @return int
	 */
	public static function page_id() {
		$header = self::header();
		return (int) $header['id'];
	}

	/**
	 * Accessible category navigation using normal WordPress taxonomy URLs
	 * (category archives themselves keep the theme presentation).
	 *
	 * @return void
	 */
	public static function category_nav() {
		if ( ! apply_filters( 'ngcv_show_category_nav', true ) ) {
			return;
		}

		$terms = get_categories(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'number'     => 15,
			)
		);
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		if ( 'home' === self::mode() ) {
			$posts_page = absint( get_option( 'page_for_posts' ) );
			$all_url    = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
		} else {
			$all_url = get_permalink( get_queried_object_id() );
		}

		echo '<nav class="ngcv-cat-nav" aria-label="' . esc_attr__( 'Browse articles by category', 'nubian-geographic-content-views' ) . '">';
		echo '<ul class="ngcv-cat-list">';
		echo '<li><a class="ngcv-cat-link is-current" href="' . esc_url( $all_url ) . '" aria-current="page">' . esc_html__( 'All', 'nubian-geographic-content-views' ) . '</a></li>';
		foreach ( $terms as $term ) {
			$link = get_category_link( $term );
			if ( ! $link || is_wp_error( $link ) ) {
				continue;
			}
			echo '<li><a class="ngcv-cat-link" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></li>';
		}
		echo '</ul>';
		echo '</nav>';
	}

	/**
	 * Native, crawlable pagination for the grid (list markup).
	 *
	 * @return void
	 */
	public static function pagination() {
		$query = self::posts_query();
		if ( ! $query || (int) $query->max_num_pages < 2 ) {
			return;
		}

		$big = 999999999; // Out-of-range page number used to build the base URL.

		$html = paginate_links(
			array(
				'base'      => str_replace( (string) $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, (int) get_query_var( 'paged' ) ),
				'total'     => (int) $query->max_num_pages,
				'type'      => 'list',
				'prev_text' => '<span class="ngcv-arrow" aria-hidden="true">&larr;</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'nubian-geographic-content-views' ) . '</span>',
				'next_text' => '<span class="ngcv-arrow" aria-hidden="true">&rarr;</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'nubian-geographic-content-views' ) . '</span>',
			)
		);

		if ( $html ) {
			echo '<nav class="ngcv-pagination" aria-label="' . esc_attr__( 'Articles pagination', 'nubian-geographic-content-views' ) . '">';
			echo wp_kses_post( $html );
			echo '</nav>';
		}
	}
}

