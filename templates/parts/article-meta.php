<?php
/**
 * Article metadata part.
 *
 * 'part' => 'categories' renders the category chip row (single header).
 * 'part' => 'meta' renders author / date / updated / reading time.
 * Only fields that actually exist in WordPress are shown; nothing invented.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_part = isset( $part ) ? (string) $part : 'meta';

if ( 'categories' === $ngcv_part ) :
	$ngcv_cats = get_the_category();
	if ( empty( $ngcv_cats ) ) {
		return;
	}
	?>
	<nav class="ngcv-article-cats" aria-label="<?php esc_attr_e( 'Article categories', 'nubian-geographic-content-views' ); ?>">
		<ul class="ngcv-cats-list">
			<?php foreach ( $ngcv_cats as $ngcv_cat ) : ?>
				<?php $ngcv_cat_link = get_category_link( $ngcv_cat ); ?>
				<?php if ( ! $ngcv_cat_link || is_wp_error( $ngcv_cat_link ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li><a class="ngcv-chip ngcv-label" href="<?php echo esc_url( $ngcv_cat_link ); ?>" rel="category"><?php echo esc_html( $ngcv_cat->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
	return;
endif;

$ngcv_reading   = NGCV_Single::reading_time( get_the_ID() );
$ngcv_author    = (string) get_the_author();
$ngcv_updated   = (int) get_the_modified_date( 'U' );
$ngcv_published = (int) get_the_date( 'U' );

// Build the line from the fields that actually exist, so separators are never
// left orphaned when a field is empty.
$ngcv_items = array();

if ( '' !== trim( $ngcv_author ) ) {
	$ngcv_items[] = '<span class="ngcv-meta-author"><a href="' . esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( $ngcv_author ) . '</a></span>';
}

$ngcv_items[] = '<time class="ngcv-meta-date" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';

if ( $ngcv_updated && $ngcv_published && ( $ngcv_updated - $ngcv_published ) > DAY_IN_SECONDS ) {
	$ngcv_items[] = '<time class="ngcv-meta-updated" datetime="' . esc_attr( get_the_modified_date( DATE_W3C ) ) . '">'
		. esc_html( sprintf( /* translators: %s: date of the last update. */ __( 'Updated %s', 'nubian-geographic-content-views' ), get_the_modified_date() ) )
		. '</time>';
}

if ( $ngcv_reading ) {
	$ngcv_items[] = '<span class="ngcv-readtime">'
		. esc_html( sprintf( _n( '%s min read', '%s min read', $ngcv_reading, 'nubian-geographic-content-views' ), number_format_i18n( $ngcv_reading ) ) )
		. '</span>';
}

if ( empty( $ngcv_items ) ) {
	return;
}
?>
<div class="ngcv-article-meta">
	<?php echo wp_kses_post( implode( '<span class="ngcv-dot" aria-hidden="true">&middot;</span>', $ngcv_items ) ); ?>
</div>
