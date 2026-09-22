<?php
/**
 * Related articles part.
 *
 * Existing categories (fallback: tags), current post excluded, limited to 3,
 * language-scoped via Polylang. Renders nothing when there are no matches.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_related = NGCV_Single::related_query();

if ( ! $ngcv_related ) {
	return;
}
?>
<section class="ngcv-related" aria-labelledby="ngcv-related-title">
	<h2 id="ngcv-related-title" class="ngcv-section-title"><?php esc_html_e( 'Related Articles', 'nubian-geographic-content-views' ); ?></h2>
	<div class="ngcv-related-grid">
		<?php while ( $ngcv_related->have_posts() ) : ?>
			<?php $ngcv_related->the_post(); ?>
			<?php ngcv_get_template( 'parts/article-card.php', array( 'variant' => 'standard', 'heading_level' => 3 ) ); ?>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</section>
