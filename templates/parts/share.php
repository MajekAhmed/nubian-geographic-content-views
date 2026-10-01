<?php
/**
 * Article sharing controls.
 *
 * Shares the current article's canonical URL and title only. Plain share
 * links (no SDKs, no tracking): Facebook, X, WhatsApp, Telegram, LinkedIn —
 * plus a copy-link control and a Web Share API control (progressively
 * enhanced, hidden without JS support). All URLs/titles are safely encoded.
 *
 * Template vars:
 *   $post_id int Article ID (defaults to the current post).
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

$ngcv_post_id = isset( $post_id ) ? absint( $post_id ) : (int) get_the_ID();
if ( ! $ngcv_post_id ) {
	return;
}

$ngcv_url   = get_permalink( $ngcv_post_id );
$ngcv_title = get_the_title( $ngcv_post_id );
if ( ! $ngcv_url || '' === trim( (string) $ngcv_title ) ) {
	return;
}

// Safe encoding for query-string targets.
$ngcv_enc_url   = rawurlencode( $ngcv_url );
$ngcv_enc_title = rawurlencode( html_entity_decode( wp_strip_all_tags( (string) $ngcv_title ), ENT_QUOTES, 'UTF-8' ) );

$ngcv_links = array(
	'facebook' => array(
		'label' => __( 'Share on Facebook', 'nubian-geographic-content-views' ),
		'href'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $ngcv_enc_url,
	),
	'x'        => array(
		'label' => __( 'Share on X', 'nubian-geographic-content-views' ),
		'href'  => 'https://twitter.com/intent/tweet?url=' . $ngcv_enc_url . '&text=' . $ngcv_enc_title,
	),
	'whatsapp' => array(
		'label' => __( 'Share on WhatsApp', 'nubian-geographic-content-views' ),
		'href'  => 'https://wa.me/?text=' . $ngcv_enc_title . '%20' . $ngcv_enc_url,
	),
	'telegram' => array(
		'label' => __( 'Share on Telegram', 'nubian-geographic-content-views' ),
		'href'  => 'https://t.me/share/url?url=' . $ngcv_enc_url . '&text=' . $ngcv_enc_title,
	),
	'linkedin' => array(
		'label' => __( 'Share on LinkedIn', 'nubian-geographic-content-views' ),
		'href'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $ngcv_enc_url,
	),
);
?>
<section class="ngcv-share" aria-labelledby="ngcv-share-title" data-ngcv-share data-ngcv-share-url="<?php echo esc_attr( $ngcv_url ); ?>" data-ngcv-share-title="<?php echo esc_attr( $ngcv_title ); ?>">
	<h2 id="ngcv-share-title" class="ngcv-share-title ngcv-label"><?php esc_html_e( 'Share this article', 'nubian-geographic-content-views' ); ?></h2>

	<ul class="ngcv-share-list">
		<?php foreach ( $ngcv_links as $ngcv_key => $ngcv_link ) : ?>
			<li class="ngcv-share-item ngcv-share-item--<?php echo esc_attr( $ngcv_key ); ?>">
				<a class="ngcv-share-link" href="<?php echo esc_url( $ngcv_link['href'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $ngcv_link['label'] ); ?>">
					<span class="ngcv-share-icon ngcv-share-icon--<?php echo esc_attr( $ngcv_key ); ?>" aria-hidden="true"></span>
					<span class="ngcv-share-text"><?php echo esc_html( $ngcv_link['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>

		<li class="ngcv-share-item ngcv-share-item--copy">
			<button type="button" class="ngcv-share-link ngcv-share-copy" data-ngcv-copy data-ngcv-copied="<?php esc_attr_e( 'Copied', 'nubian-geographic-content-views' ); ?>" data-ngcv-failed="<?php esc_attr_e( 'Copy failed', 'nubian-geographic-content-views' ); ?>" aria-label="<?php esc_attr_e( 'Copy article link', 'nubian-geographic-content-views' ); ?>">
				<span class="ngcv-share-icon ngcv-share-icon--copy" aria-hidden="true"></span>
				<span class="ngcv-share-text"><?php esc_html_e( 'Copy link', 'nubian-geographic-content-views' ); ?></span>
			</button>
			<span class="ngcv-share-status" data-ngcv-copy-status role="status" aria-live="polite"></span>
		</li>

		<li class="ngcv-share-item ngcv-share-item--native" data-ngcv-native hidden>
			<button type="button" class="ngcv-share-link ngcv-share-native" data-ngcv-native-button aria-label="<?php esc_attr_e( 'Share via your device', 'nubian-geographic-content-views' ); ?>">
				<span class="ngcv-share-icon ngcv-share-icon--native" aria-hidden="true"></span>
				<span class="ngcv-share-text"><?php esc_html_e( 'More options', 'nubian-geographic-content-views' ); ?></span>
			</button>
		</li>
	</ul>
</section>
