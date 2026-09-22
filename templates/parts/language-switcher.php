<?php
/**
 * Language switcher part.
 *
 * Built from Polylang's existing language relationships (read-only). Languages
 * without a translation for the current post/page are never linked; the
 * switcher is suppressed entirely when there is nothing useful to show.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_post_id = isset( $post_id ) ? absint( $post_id ) : 0;
$ngcv_langs   = NGCV_Polylang::language_switcher( $ngcv_post_id );

if ( empty( $ngcv_langs ) ) {
	return;
}
?>
<nav class="ngcv-lang-nav" aria-label="<?php esc_attr_e( 'Available languages', 'nubian-geographic-content-views' ); ?>">
	<ul class="ngcv-lang-list">
		<?php foreach ( $ngcv_langs as $ngcv_lang ) : ?>
			<?php
			$ngcv_label = '' !== $ngcv_lang['name'] ? $ngcv_lang['name'] : $ngcv_lang['slug'];
			$ngcv_slug  = sanitize_html_class( $ngcv_lang['slug'] );
			?>
			<?php if ( $ngcv_lang['current'] ) : ?>
				<li class="ngcv-lang-item is-current">
					<span class="ngcv-lang-current" aria-current="true"><?php echo esc_html( $ngcv_label ); ?></span>
				</li>
			<?php else : ?>
				<li class="ngcv-lang-item">
					<a class="ngcv-lang-link" href="<?php echo esc_url( $ngcv_lang['url'] ); ?>"
						<?php if ( $ngcv_slug ) : ?>lang="<?php echo esc_attr( $ngcv_lang['slug'] ); ?>" hreflang="<?php echo esc_attr( $ngcv_lang['slug'] ); ?>"<?php endif; ?>>
						<?php echo esc_html( $ngcv_label ); ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</nav>
