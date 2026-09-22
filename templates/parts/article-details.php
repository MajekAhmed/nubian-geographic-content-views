<?php
/**
 * Article details — structured, restrained metadata.
 *
 * Only data that already exists is rendered: the current Polylang language
 * name, and the article's own length. No citations, credentials, references,
 * sources, or dates are invented, and nothing here is duplicated from the
 * header meta line.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_post_id  = (int) get_the_ID();
$ngcv_language = NGCV_Polylang::current_language( 'name' );
$ngcv_words    = NGCV_Single::word_count( $ngcv_post_id );
$ngcv_reading  = NGCV_Single::reading_time( $ngcv_post_id );

if ( '' === $ngcv_language && $ngcv_words < 1 ) {
	return;
}
?>
<section class="ngcv-details" aria-labelledby="ngcv-details-title">
	<h2 id="ngcv-details-title" class="ngcv-details-title ngcv-label"><?php esc_html_e( 'Article details', 'nubian-geographic-content-views' ); ?></h2>

	<dl class="ngcv-details-list">
		<?php if ( '' !== $ngcv_language ) : ?>
			<div class="ngcv-details-item">
				<dt class="ngcv-details-label"><?php esc_html_e( 'Language', 'nubian-geographic-content-views' ); ?></dt>
				<dd class="ngcv-details-value"><?php echo esc_html( $ngcv_language ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( $ngcv_words > 0 ) : ?>
			<div class="ngcv-details-item">
				<dt class="ngcv-details-label"><?php esc_html_e( 'Length', 'nubian-geographic-content-views' ); ?></dt>
				<dd class="ngcv-details-value">
					<?php echo esc_html( sprintf( _n( '%s word', '%s words', $ngcv_words, 'nubian-geographic-content-views' ), number_format_i18n( $ngcv_words ) ) ); ?>
					<?php if ( $ngcv_reading ) : ?>
						<span class="ngcv-dot" aria-hidden="true">&middot;</span>
						<?php echo esc_html( sprintf( _n( '%s min read', '%s min read', $ngcv_reading, 'nubian-geographic-content-views' ), number_format_i18n( $ngcv_reading ) ) ); ?>
					<?php endif; ?>
				</dd>
			</div>
		<?php endif; ?>
	</dl>
</section>