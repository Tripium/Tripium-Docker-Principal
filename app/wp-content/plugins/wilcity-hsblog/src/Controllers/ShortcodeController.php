<?php

namespace WilcityHsBlog\Controllers;

use Elementor\Plugin;
use WILCITY_SC\ParseShortcodeAtts\ParseShortcodeAtts;
use WILCITY_SC\SCHelpers;
use WilcityHsBlog\Helpers\App;
use WilcityHsBlog\Controllers\RectangleTermBoxes;

final class ShortcodeController
{
	private $elAjaxSearchCatsAction = 'el_search_hs_cats';

	public function __construct()
	{
		add_filter('wilcity/filter/wilcity-shortcodes/config/commom_shortcodes', [$this, 'addSelectHsBlogCategories']);
		add_filter('kc_autocomplete_hsblog_cats', [$this, 'fetchHsBlogCatForKC']);
		add_filter('wilcity/filter/wilcity-shortcodes/app/RegisterSC/configurations', [$this, 'registerHsBlogSC']);
		add_action('init', [$this, 'addKCShortcodeLocate'], 99);
		add_action('elementor/widgets/widgets_registered', [$this, 'registerWidgets']);
		add_action('wp_ajax_' . $this->elAjaxSearchCatsAction, [$this, 'handleElSearchForHsCategories']);
		add_filter(
			'vc_autocomplete_wilcity_vc_hsblog_cats_hsblog_cats_callback',
			[$this, 'searchHsBlogCats'],
			10,
			3
		);
		add_filter(
			'vc_autocomplete_wilcity_vc_hsblog_cats_hsblog_cats_render',
			[$this, 'renderHsBlogCats'],
			10,
			2
		);
		add_action('vc_before_init', [$this, 'registerVc']);
	}

	public function registerWidgets()
	{
		Plugin::instance()->widgets_manager->register_widget_type(new RectangleTermBoxes());
	}

	private function fetchCategories($aArgs = []): array
	{
		if (!empty($aArgs)) {
			$url = add_query_arg($aArgs, App::getEndpoint('categories'));
		} else {
			$url = App::getEndpoint('categories');
		}

		$response = wp_remote_get($url);

		if (wp_remote_retrieve_response_code($response) !== 200) {
			return [];
		}

		$aItems = json_decode(wp_remote_retrieve_body($response), true);

		return $aItems['items'];
	}

	public function searchHsBlogCats($s, $tag, $param_name): array
	{
		$aResponse = $this->fetchCategories(['s' => $s]);

		if (empty($aResponse)) {
			return [];
		}

		$aOptions = [];
		foreach ($aResponse as $aTerm) {
			$aOptions[] = [
				'label' => $aTerm['name'],
				'value' => $aTerm['term_id'],
			];
		}

		return $aOptions;
	}


	public function renderHsBlogCats($currentVal, $aParamSettings): array
	{
		if (empty($currentVal)) {
			return [];
		}

		$aResponse = $this->fetchCategories(['ids' => implode(',', $currentVal['value'])]);
		$aOptions = [];

		foreach ($aResponse as $aTerm) {
			$aOptions = [
				'label' => $aTerm['name'],
				'value' => $aTerm['term_id'],
			];
		}

		return $aOptions;
	}

	public function renderRectangleTermBoxes(array $aAtts)
	{
		$oParseSc = new ParseShortcodeAtts($aAtts);
		$oParseSc->parseColumnClasses();
		$aAtts = $oParseSc->getSCAttributes();

		$aTermIds = SCHelpers::getAutoCompleteVal($aAtts['hsblog_cats']);
		$wrapperClasses = apply_filters('wilcity-el-class', $aAtts);
		$wrapperClasses = implode(' ', $wrapperClasses) . '  ' . $aAtts['extra_class'] . ' wil-masonry_module__hEqFd';

		$aAtts['singular_text'] = esc_html__('Article', 'wilcity-hsblog');
		$aAtts['plural_text'] = esc_html__('Articles', 'wilcity-hsblog');
		$aAtts['is_external_term'] = true;
		$aAtts['target'] = '_blank';

		if (!empty($aTermIds)) {
			$aTerms = $this->fetchCategories(['ids' => implode(',', $aTermIds)]);
			?>
            <div class="<?php echo esc_attr($wrapperClasses); ?>">
				<?php
				if (!empty($aAtts['heading']) || !empty($aAtts['desc'])) {
					wilcity_render_heading([
						'TYPE'            => 'HEADING',
						'blur_mark'       => '',
						'blur_mark_color' => '',
						'heading'         => $aAtts['heading'],
						'heading_color'   => $aAtts['heading_color'],
						'desc'            => $aAtts['desc'],
						'desc_color'      => $aAtts['desc_color'],
						'alignment'       => $aAtts['header_desc_text_align'],
						'extra_class'     => ''
					]);
				}
				?>
                <div class="row" data-col-xs-gap="10" data-totals="<?php echo esc_attr(count($aTerms)); ?>">
					<?php
					foreach ($aTerms as $oTerm) {
						wilcity_render_rectangle_term_box((object)$oTerm, $aAtts);
					} ?>
                </div>
            </div>
			<?php
		}
	}

	public function addKCShortcodeLocate()
	{
		global $kc, $wilcityKcTemplateRepository;

		$wilcityKcTemplateRepository = wilcityShortcodesRepository(WILCITY_SC_DIR . 'configs/sc-attributes/');

		if (!function_exists('kc_add_map')) {
			return false;
		}

		$kc->set_template_path(WILCITY_HSBLOG_DIR . 'src/Views/KC/');
	}

	public function registerHsBlogSC($aSC): array
	{
		$aConfigs = include WILCITY_HSBLOG_DIR . 'configs/sc/shortcodes.php';
		$aSC = array_merge($aSC, $aConfigs);

		return $aSC;
	}


	public function registerVc()
	{
		$fileDir = WILCITY_HSBLOG_DIR . 'src/Views/VC/wilcity_vc_hsblog_cats.php';

		if (is_file($fileDir)) {
			include $fileDir;
		}
	}

	public function fetchHsBlogCatForKC($aData)
	{
		$s = isset($aData['s']) ? $aData['s'] : '';

		$aRawTerms = $this->fetchCategories(['s' => $s]);
		if (!$aRawTerms) {
			return false;
		}

		$aTerms = [];
		foreach ($aRawTerms as $aTerm) {
			$aTerms[] = $aTerm['term_id'] . ':' . $aTerm['name'];
		}

		return ['Select Terms' => $aTerms];
	}

	public function addSelectHsBlogCategories($aCommonShortcodeItems)
	{
		$aCommonShortcodeItems['item']['hsblog_cats'] = [
			'type'               => 'autocomplete',
			'el_type'            => 'select2_ajax',
			'multiple'           => true,
			'label'              => 'Select HsBlog Categories',
			'name'               => 'hsblog_cats',
			'placeholder'        => esc_html__('Enter a keyword to search for category', 'wilcity-blog'),
			'minimumInputLength' => 1,
			'endpoint'           => App::getEndpoint('categories', true),
			// can't use args: search and page on this field
			'api_args'           => [
				'per_page' => 20,
			]
		];
		return $aCommonShortcodeItems;
	}
}
