<?php
/**
 * NGCV — Single Article.
 *
 * Premium editorial article view for the site's existing posts. The article
 * body is served through the native WordPress content pipeline (server-rendered,
 * blocks/galleries/embeds preserved). Exactly one <h1> (the article title);
 * no SEO meta and no structured data are emitted.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

get_header();

NGCV_Plugin::shell_open( NGCV_Plugin::CONTEXT_SINGLE );

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		// Run the full content pipeline once (collects TOC headings).
		NGCV_Single::prepare_content();
		$ngcv_has_toc = NGCV_Single::should_render_toc();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngcv-article' ); ?>>
			<div class="ngcv-article-inner">
				<header class="ngcv-article-header">
					<?php ngcv_get_template( 'parts/breadcrumbs.php' ); ?>

					<?php ngcv_get_template( 'parts/article-meta.php', array( 'part' => 'categories' ) ); ?>

					<h1 class="ngcv-article-title"><?php the_title(); ?></h1>

					<?php if ( NGCV_Single::has_manual_excerpt() ) : ?>
						<p class="ngcv-article-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<?php ngcv_get_template( 'parts/article-meta.php', array( 'part' => 'meta' ) ); ?>

					<?php ngcv_get_template( 'parts/language-counterpart.php', array( 'post_id' => get_the_ID() ) ); ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="ngcv-article-hero">
						<div class="ngcv-media">
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
						</div>
						<?php $ngcv_caption = get_the_post_thumbnail_caption(); ?>
						<?php if ( $ngcv_caption ) : ?>
							<figcaption class="ngcv-article-hero-caption"><?php echo esc_html( $ngcv_caption ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="ngcv-article-layout<?php echo $ngcv_has_toc ? ' ngcv-article-layout--with-aside' : ''; ?>">
					<?php if ( $ngcv_has_toc ) : ?>
						<aside class="ngcv-article-aside">
							<?php ngcv_get_template( 'parts/table-of-contents.php', array( 'items' => NGCV_Single::toc_items() ) ); ?>
						</aside>
					<?php endif; ?>

					<div class="ngcv-article-main">
						<div class="ngcv-article-body ngcv-prose">
							<?php NGCV_Single::print_content(); ?>
							<?php
							wp_link_pages(
								array(
									'before'      => '<nav class="ngcv-page-links" aria-label="' . esc_attr__( 'Article pages', 'nubian-geographic-content-views' ) . '"><span class="ngcv-page-links-label">' . esc_html__( 'Pages:', 'nubian-geographic-content-views' ) . '</span>',
									'after'       => '</nav>',
									'link_before' => '<span class="ngcv-page-links-item">',
									'link_after'  => '</span>',
								)
							);
							?>
						</div>

						<footer class="ngcv-article-footer">
							<?php ngcv_get_template( 'parts/article-details.php' ); ?>

							<?php
							$ngcv_tags_list = get_the_tag_list( '<ul class="ngcv-tags" aria-label="' . esc_attr__( 'Topics', 'nubian-geographic-content-views' ) . '"><li>', '</li><li>', '</li></ul>' );
							if ( $ngcv_tags_list && ! is_wp_error( $ngcv_tags_list ) ) {
								echo $ngcv_tags_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress-generated tag links.
							}
							?>
						</footer>
					</div>
				</div>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			</div>
		</article>

		<?php NGCV_Single::post_navigation(); ?>

		<?php ngcv_get_template( 'parts/related-articles.php' ); ?>

		<?php ngcv_get_template( 'parts/language-switcher.php', array( 'post_id' => get_the_ID() ) ); ?>

		<?php
	endwhile;
endif;

NGCV_Plugin::shell_close( NGCV_Plugin::CONTEXT_SINGLE );

get_footer();