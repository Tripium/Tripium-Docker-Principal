<?php

namespace WilcityHsBlog\Controllers;

use Elementor\Widget_Base;
use WILCITY_ELEMENTOR\Registers\Helpers;

class RectangleTermBoxes extends Widget_Base
{
	use Helpers;

	public function get_name()
	{
		return 'wilcity-hs-rectangle-term-boxes';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since  1.1.0
	 *
	 * @access public
	 *
	 */
	public function get_title()
	{
		return WILCITY_EL_PREFIX . 'HSBlog Rectangle Term Boxes';
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.1.0
	 *
	 * @access public
	 *
	 */
	public function get_icon()
	{
		return 'fa fa-picture-o';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @return array Widget categories.
	 * @since  1.1.0
	 *
	 * @access public
	 *
	 */
	public function get_categories()
	{
		return ['theme-elements'];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.1.0
	 *
	 * @access protected
	 */
	protected function _register_controls()
	{
		/** @var \WilcityShortcodeRepository $wilcityScRepository */
		global $wilcityScRepository;

		$aConfigs = include WILCITY_HSBLOG_DIR . 'configs/sc/shortcodes.php';

		$this->convertKCToEl($aConfigs['wilcity_kc_hsblog_cats']['params'])->registerShortcode();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.1.0
	 *
	 * @access protected
	 */
	protected function render()
	{
		/** @var \WilcityShortcodeRepository $wilcityScAttsRepository */
		global $aWilcityHsBlogObjects, $wilcityScAttsRepository;
		$aSettings = wp_parse_args(
			$this->get_settings(),
			$wilcityScAttsRepository->get($this->findScAttributesFileName(basename(__FILE__)))
		);

		$aWilcityHsBlogObjects['ShortcodeController']->renderRectangleTermBoxes($aSettings);
	}
}
