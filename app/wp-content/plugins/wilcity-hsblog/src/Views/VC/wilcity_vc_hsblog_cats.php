<?php
function wilcityVCHSBlogRectangleTermBoxes($atts)
{
	global $aWilcityHsBlogObjects;
	$aAtts = include WILCITY_HSBLOG_DIR . 'configs/sc/shortcodes-attributes.php';
	$atts = shortcode_atts(
		$aAtts['hsblog_cats'],
		$atts
	);

	$atts = apply_filters('wilcity/vc/parse_sc_atts', $atts);

	ob_start();
	$aWilcityHsBlogObjects['ShortcodeController']->renderRectangleTermBoxes($atts);
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

add_shortcode('wilcity_vc_hsblog_cats', 'wilcityVCHSBlogRectangleTermBoxes');

