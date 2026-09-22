<?php
/**
 * Language counterpart links (single article header).
 *
 * Compact, real links to the post's existing Polylang translations. Renders
 * nothing when there is no counterpart, so no broken or fabricated links are
 * ever produced. The full switcher is still rendered at the end of the article.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_post_id = isset( $post_id ) ? absint( $post_id ) : 0;
$ngcv_links   = NGCV_Polylang::counterpart_link( $ngcv_post_id );

if ( empty( $ngcv_links ) ) {
	return;
}
?>
<nav class="ngcv-counterpart" aria-label="<?php esc_attr_e( 'This article in another language', 'nubian-geographic-content-views' ); ?>">
	<ul class="ngcv-counterpart-list">
		<?php foreach ( $ngcv_links as $ngcv_lang ) : ?>
			<?php
			$ngcv_label = '' !== $ngcv_lang['name'] ? $ngcv_lang['name'] : $ngcv_lang['slug'];
			$ngcv_slug  = sanitize_html_class( $ngcv_lang['slug'] );
			?>
			<li>
				<a class="ngcv-counterpart-link" href="<?php echo esc_url( $ngcv_lang['url'] ); ?>"
					<?php if ( $ngcv_slug ) : ?>lang="<?php echo esc_attr( $ngcv_lang['slug'] ); ?>" hreflang="<?php echo esc_attr( $ngcv_lang['slug'] ); ?>"<?php endif; ?>>
					<?php echo esc_html( $ngcv_label ); ?>
					<span class="ngcv-arrow" aria-hidden="true">&rarr;</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>