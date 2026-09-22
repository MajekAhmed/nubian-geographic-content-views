<?php
/**
 * NGCV — Articles Archive.
 *
 * Presents the existing Articles page: the page keeps its ID, slug, URL and
 * content. Its title becomes the archive masthead, its own content becomes the
 * editorial introduction, and the page's existing posts are presented as a
 * featured story plus a rhythm-driven discovery grid with native pagination.
 * Breadcrumbs come from the theme's own pipeline (Rank Math-aware).
 *
 * Nothing here creates content: every article, category, date and count is
 * read from the existing WordPress data for the current language.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

get_header();

NGCV_Plugin::shell_open( NGCV_Plugin::CONTEXT_ARCHIVE );

$ngcv_header  = NGCV_Archive::header();
$ngcv_count   = NGCV_Archive::found_posts();
$ngcv_feature = NGCV_Archive::has_feature();
?>
<div class="ngcv-archive">
	<header class="ngcv-masthead">
		<?php ngcv_get_template( 'parts/breadcrumbs.php' ); ?>

		<p class="ngcv-masthead-kicker ngcv-label"><?php echo esc_html_x( 'Archive', 'Archive masthead kicker', 'nubian-geographic-content-views' ); ?></p>

		<h1 class="ngcv-archive-title"><?php echo esc_html( $ngcv_header['title'] ); ?></h1>

		<?php if ( $ngcv_count > 0 ) : ?>
			<p class="ngcv-masthead-count">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: total number of published articles. */
						_n( '%s article', '%s articles', $ngcv_count, 'nubian-geographic-content-views' ),
						number_format_i18n( $ngcv_count )
					)
				);
				?>
			</p>
		<?php endif; ?>

		<?php ngcv_get_template( 'parts/language-switcher.php', array( 'post_id' => NGCV_Archive::page_id() ) ); ?>

		<span class="ngcv-motif" aria-hidden="true"></span>
	</header>

	<?php $ngcv_intro = NGCV_Archive::intro_html(); ?>
	<?php if ( '' !== trim( $ngcv_intro ) ) : ?>
		<div class="ngcv-deck"><?php echo $ngcv_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- processed page content, same trust level as the_content(). ?></div>
	<?php endif; ?>

	<?php NGCV_Archive::category_nav(); ?>

	<?php if ( $ngcv_feature ) : ?>
		<?php NGCV_Archive::feature(); ?>
	<?php endif; ?>

	<?php if ( NGCV_Archive::has_posts() ) : ?>
		<section class="ngcv-discovery" aria-labelledby="ngcv-grid-title">
			<h2 id="ngcv-grid-title" class="ngcv-section-title ngcv-grid-title">
				<?php
				echo esc_html(
					$ngcv_feature
						? __( 'More stories', 'nubian-geographic-content-views' )
						: __( 'Latest stories', 'nubian-geographic-content-views' )
				);
				?>
			</h2>

			<div class="ngcv-grid">
				<?php NGCV_Archive::grid(); ?>
			</div>
		</section>

		<?php ngcv_get_template( 'parts/pagination.php' ); ?>
	<?php elseif ( ! $ngcv_feature ) : ?>
		<div class="ngcv-empty">
			<h2 class="ngcv-empty-title"><?php esc_html_e( 'No articles yet', 'nubian-geographic-content-views' ); ?></h2>
		</div>
	<?php endif; ?>
</div>
<?php

NGCV_Plugin::shell_close( NGCV_Plugin::CONTEXT_ARCHIVE );

get_footer();