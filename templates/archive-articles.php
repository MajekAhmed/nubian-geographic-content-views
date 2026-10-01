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

$ngcv_header    = NGCV_Archive::header();
$ngcv_count     = NGCV_Archive::found_posts();
$ngcv_feature   = NGCV_Archive::has_feature();
// Hero image order: NGCV settings image → Articles page featured image → plain.
$ngcv_hero_id   = NGCV_Settings::hero_image_id();
if ( ! $ngcv_hero_id ) {
	$ngcv_hero_id = (int) get_post_thumbnail_id( $ngcv_header['id'] );
}
$ngcv_hero_bg   = $ngcv_hero_id
	? sprintf(
		'url("%s")',
		esc_url( (string) wp_get_attachment_image_url( $ngcv_hero_id, 'ngcv-hero' ) )
	)
	: 'none';
$ngcv_deck_trim = '' !== trim( $ngcv_header['intro_html'] );
$ngcv_intro     = $ngcv_header['intro_html'];
?>
<div class="ngcv-archive">
	<header
		class="ngcv-masthead ngcv-masthead--hero<?php echo $ngcv_deck_trim ? ' ngcv-masthead--has-deck' : ''; ?>"
		style="--ngcv-hero-img: <?php echo $ngcv_hero_bg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url() applied above. ?>"
	>
		<div class="ngcv-masthead-inner ngcv-container">
			<div class="ngcv-masthead-copy ngcv-enter">
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

			<?php if ( $ngcv_deck_trim ) : ?>
				<p class="ngcv-masthead-deck">
					<?php echo wp_kses_post( wp_trim_words( wp_strip_all_tags( $ngcv_header['intro_html'] ), 30 ) ); ?>
				</p>
			<?php endif; ?>
		</div>

		<span class="ngcv-motif" aria-hidden="true"></span>
	</div>
</header>

	<div class="ngcv-container">
		<?php if ( '' !== trim( $ngcv_intro ) ) : ?>
			<div class="ngcv-deck<?php echo $ngcv_deck_trim ? ' ngcv-deck--trimmed' : ''; ?>"><?php echo $ngcv_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- processed page content, same trust level as the_content(). ?></div>
		<?php endif; ?>

		<?php NGCV_Archive::category_nav(); ?>

		<?php if ( $ngcv_feature ) : ?>
			<?php NGCV_Archive::feature(); ?>
		<?php endif; ?>

		<?php if ( NGCV_Archive::has_posts() ) : ?>
			<section class="ngcv-discovery" aria-labelledby="ngcv-grid-title">
				<div class="ngcv-divider">
					<h2 id="ngcv-grid-title" class="ngcv-divider-title">
						<?php
						echo esc_html(
							$ngcv_feature
								? __( 'More stories', 'nubian-geographic-content-views' )
								: __( 'Latest stories', 'nubian-geographic-content-views' )
						);
						?>
					</h2>
				</div>

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
</div>
<?php

NGCV_Plugin::shell_close( NGCV_Plugin::CONTEXT_ARCHIVE );

get_footer();