<?php
/**
 * Article card (archive grid + related articles).
 *
 * Semantic, fully keyboard-accessible card: the image link is decorative
 * (aria-hidden, not tabbable) and the title link is the primary target.
 * All displayed data already exists in WordPress — nothing is invented.
 *
 * Template vars:
 *   $variant       'standard' | 'lead' | 'tall' — editorial rhythm in the grid.
 *   $heading_level 2–4, matching the surrounding heading hierarchy.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_variant = isset( $variant ) ? sanitize_html_class( (string) $variant ) : 'standard';
if ( ! in_array( $ngcv_variant, array( 'standard', 'lead', 'tall' ), true ) ) {
	$ngcv_variant = 'standard';
}

$ngcv_level = isset( $heading_level ) ? (int) $heading_level : 2;
$ngcv_level = max( 2, min( 4, $ngcv_level ) );

$ngcv_permalink  = get_permalink();
$ngcv_categories = get_the_category();
$ngcv_primary    = ! empty( $ngcv_categories ) ? $ngcv_categories[0] : null;
$ngcv_reading    = NGCV_Single::reading_time( get_the_ID() );
$ngcv_has_media  = has_post_thumbnail();

$ngcv_classes = array( 'ngcv-article-card', 'ngcv-card--' . $ngcv_variant );
if ( ! $ngcv_has_media ) {
	$ngcv_classes[] = 'ngcv-card--no-media';
}

// 'tall' uses the portrait crop; everything else the landscape card crop.
$ngcv_image_size  = ( 'tall' === $ngcv_variant ) ? 'ngcv-card-tall' : 'ngcv-card';
$ngcv_image_sizes = NGCV_Assets::image_sizes_attr( 'lead' === $ngcv_variant ? 'lead' : $ngcv_variant );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $ngcv_classes ); ?>>
	<?php if ( $ngcv_has_media ) : ?>
		<a class="ngcv-card-media ngcv-media" href="<?php echo esc_url( $ngcv_permalink ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			the_post_thumbnail(
				$ngcv_image_size,
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => $ngcv_image_sizes,
				)
			);
			?>
		</a>
	<?php endif; ?>

	<div class="ngcv-card-body">
		<?php if ( $ngcv_primary instanceof WP_Term ) : ?>
			<?php $ngcv_cat_link = get_category_link( $ngcv_primary ); ?>
			<?php if ( $ngcv_cat_link && ! is_wp_error( $ngcv_cat_link ) ) : ?>
				<div class="ngcv-card-cat">
					<a class="ngcv-chip ngcv-label" href="<?php echo esc_url( $ngcv_cat_link ); ?>" rel="category"><?php echo esc_html( $ngcv_primary->name ); ?></a>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<h<?php echo (int) $ngcv_level; ?> class="ngcv-card-title">
			<a href="<?php echo esc_url( $ngcv_permalink ); ?>"><?php the_title(); ?></a>
		</h<?php echo (int) $ngcv_level; ?>>

		<div class="ngcv-card-excerpt"><?php the_excerpt(); ?></div>

		<div class="ngcv-card-meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( $ngcv_reading ) : ?>
				<span class="ngcv-dot" aria-hidden="true">&middot;</span>
				<span class="ngcv-readtime"><?php echo esc_html( sprintf( _n( '%s min read', '%s min read', $ngcv_reading, 'nubian-geographic-content-views' ), number_format_i18n( $ngcv_reading ) ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>