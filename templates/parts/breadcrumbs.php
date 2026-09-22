<?php
/**
 * Breadcrumbs part.
 *
 * The theme renders its breadcrumb on the `digital_newspaper_before_inner_content`
 * hook, which the plugin's shell fires (so there is always exactly one trail,
 * produced by the theme's own settings-aware pipeline — Rank Math / Yoast /
 * Breadcrumb NavXT / built-in trail). This part is a fallback that renders a
 * breadcrumb ONLY if the theme hook produced none (e.g. theme changed).
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

// If the theme hook has any callbacks, the breadcrumb was already rendered above.
if ( has_action( 'digital_newspaper_before_inner_content' ) ) {
	return;
}

$ngcv_theme_breadcrumb = function_exists( 'digital_newspaper_breadcrumb_html' );
$ngcv_rank_math        = function_exists( 'rank_math_the_breadcrumbs' );

if ( ! $ngcv_theme_breadcrumb && ! $ngcv_rank_math ) {
	return;
}
?>
<div class="ngcv-breadcrumbs">
	<?php
	if ( $ngcv_theme_breadcrumb ) {
		// Theme's own switch: settings-aware, single trail, Rank Math-aware.
		digital_newspaper_breadcrumb_html();
	} else {
		// Rank Math prints nothing when breadcrumbs are disabled in its settings.
		rank_math_the_breadcrumbs();
	}
	?>
</div>
