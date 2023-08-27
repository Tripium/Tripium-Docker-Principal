<?php

namespace WILCITY_ELEMENTOR\Registers;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use WILCITY_SC\SCHelpers;

class MasonryTermBoxes extends Widget_Base
{
    use Helpers;

    public function get_name()
    {
        return WILCITY_WHITE_LABEL.'-masonry-term-boxes';
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
        return WILCITY_EL_PREFIX.'Masonry Term Boxes';
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
    protected function register_controls()
    {
        /** @var \WilcityShortcodeRepository $wilcityScRepository */
        global $wilcityScRepository;
        //
        $this->convertKCToEl(
          $wilcityScRepository->get('wilcity_kc_masonry_term_boxes:wilcity_kc_masonry_term_boxes', true)->sub('params')
        )->registerShortcode()
        ;
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
        global $wilcityScAttsRepository;
        $aSettings = wp_parse_args(
          $this->get_settings(),
          $wilcityScAttsRepository->get($this->findScAttributesFileName(basename(__FILE__)))
        );

        wilcity_render_term_masonry_items($aSettings);
    }
}
