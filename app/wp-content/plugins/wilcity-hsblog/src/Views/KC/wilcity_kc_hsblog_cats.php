<?php
/** @var WilcityShortcodeRepository $wilcityKcTemplateRepository */
global $aWilcityHsBlogObjects;
$aAtts = include WILCITY_HSBLOG_DIR . 'configs/sc/shortcodes-attributes.php';

$atts = shortcode_atts(
	$aAtts['hsblog_cats'],
	$atts
);

$aWilcityHsBlogObjects['ShortcodeController']->renderRectangleTermBoxes($atts);
