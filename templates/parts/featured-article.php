<?php
/**
 * NGCV — Featured story (archive page 1).
 *
 * Renders the first post of the archive's existing query: real title, real
 * category, real excerpt, real dates, real featured image. Rendered inside the
 * loop by NGCV_Archive::feature(); nothing is duplicated in the grid below.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_permalink  = get_permalink();
$ngcv_categories = get_the_category();
$ngcv_primary    = ! empty( $ngcv_categories ) ? $ngcv_categories[0] : null;
$ngcv_reading    = NGCV_Single::reading_time( get_the_ID() );
$ngcv_has_media  = has_post_thumbnail();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngcv-feature' ); ?> aria-labelledby="ngcv-feature-title">
	<?php if ( $ngcv_has_media ) : ?>
		<a class="ngcv-feature-media ngcv-media" href="<?php echo esc_url( $ngcv_permalink ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			the_post_thumbnail(
				'ngcv-hero',
				array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'sizes'         => NGCV_Assets::image_sizes_attr( 'hero' ),
				)
			);
			?>
		</a>
	<?php else : ?>
		<div class="ngcv-feature-media ngcv-media ngcv-media--empty" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="ngcv-feature-body">
		<p class="ngcv-feature-kicker ngcv-label"><?php esc_html_e( 'Featured story', 'nubian-geographic-content-views' ); ?></p>

		<?php if ( $ngcv_primary instanceof WP_Term ) : ?>
			<?php $ngcv_cat_link = get_category_link( $ngcv_primary ); ?>
			<?php if ( $ngcv_cat_link && ! is_wp_error( $ngcv_cat_link ) ) : ?>
				<div class="ngcv-card-cat">
					<a class="ngcv-chip ngcv-label" href="<?php echo esc_url( $ngcv_cat_link ); ?>" rel="category"><?php echo esc_html( $ngcv_primary->name ); ?></a>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<h2 id="ngcv-feature-title" class="ngcv-feature-title">
			<a href="<?php echo esc_url( $ngcv_permalink ); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="ngcv-feature-excerpt"><?php the_excerpt(); ?></div>

		<div class="ngcv-feature-meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( $ngcv_reading ) : ?>
				<span class="ngcv-dot" aria-hidden="true">&middot;</span>
				<span class="ngcv-readtime"><?php echo esc_html( sprintf( _n( '%s min read', '%s min read', $ngcv_reading, 'nubian-geographic-content-views' ), number_format_i18n( $ngcv_reading ) ) ); ?></span>
			<?php endif; ?>
		</div>

		<p class="ngcv-feature-action">
			<a class="ngcv-btn" href="<?php echo esc_url( $ngcv_permalink ); ?>">
				<?php esc_html_e( 'Read the story', 'nubian-geographic-content-views' ); ?>
				<span class="ngcv-arrow" aria-hidden="true">&rarr;</span>
			</a>
		</p>
	</div>
</article>