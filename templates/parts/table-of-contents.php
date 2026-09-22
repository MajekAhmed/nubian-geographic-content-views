<?php
/**
 * Table of contents part.
 *
 * Generated from the article's own h2/h3 headings during the content render
 * (stable IDs injected at render time only — nothing is rewritten or stored).
 * The list is fully server-rendered and visible without JavaScript; the
 * toggle button is a progressive enhancement.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_toc_items = isset( $items ) && is_array( $items ) ? $items : NGCV_Single::toc_items();

if ( empty( $ngcv_toc_items ) ) {
	return;
}
?>
<nav class="ngcv-toc" aria-labelledby="ngcv-toc-title">
	<div class="ngcv-toc-head">
		<h2 id="ngcv-toc-title" class="ngcv-toc-title ngcv-label"><?php esc_html_e( 'In this article', 'nubian-geographic-content-views' ); ?></h2>
		<button type="button" class="ngcv-toc-toggle" aria-expanded="true" aria-controls="ngcv-toc-list">
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle table of contents', 'nubian-geographic-content-views' ); ?></span>
			<span class="ngcv-toc-toggle-icon" aria-hidden="true">&#9662;</span>
		</button>
	</div>
	<ol id="ngcv-toc-list" class="ngcv-toc-list" data-ngcv-toc>
		<?php foreach ( $ngcv_toc_items as $ngcv_item ) : ?>
			<li class="ngcv-toc-item ngcv-toc-h<?php echo esc_attr( (int) $ngcv_item['level'] ); ?>">
				<a href="#<?php echo esc_attr( $ngcv_item['id'] ); ?>"><?php echo esc_html( $ngcv_item['text'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
