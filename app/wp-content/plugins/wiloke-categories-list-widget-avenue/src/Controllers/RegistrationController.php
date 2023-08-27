<?php

namespace WilokePostCategoriesAvenue\Controllers;


use Elementor\Controls_Manager;
use WilokePostCategoriesAvenue\Controllers\CategoryPostControl\CustomCategoryPostControl;
use WilokePostCategoriesAvenue\Controllers\CategoryProductControl\CustomCategoryProductControl;
use WilokePostCategoriesAvenue\Controllers\PostControl\CustomPostControl;
use WilokePostCategoriesAvenue\Controllers\ProductControl\ProductControl;
use WilokePostCategoriesAvenue\Share\App;

class RegistrationController
{
	public static string $WilokePostCategoriesAvenue = 'WilokePostCategoriesAvenue';

	public function __construct()
	{

    add_filter('elementor/frontend/print_google_fonts', '__return_true', 999);
		$aConfigs = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../Configs/config.json'), true);
		App::bind('dataConfig', $aConfigs);
		add_action('elementor/elements/categories_registered', [$this, 'registerCategories']);
		add_action('elementor/widgets/register', [$this, 'registerAddon']);
		add_action('elementor/controls/register', [$this, 'registerControls']);
		add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
	}

	public function registerCategories($oElementsManager)
	{
		$key = App::get('dataConfig')['category']['key'] ?? 'wiloke-category';
		if (!array_key_exists($key, $oElementsManager->get_categories())) {
			$oElementsManager->add_category(
				$key,
				[
					'title' => App::get('dataConfig')['category']['title'] ??
						esc_html__('Wiloke', 'wiloke-post-categories-avenue'),
					'icon'  => App::get('dataConfig')['category']['icon'] ?? 'eicon-font',
				]
			);
		}
	}

	public function registerScripts()
	{
		$aHandleCss = [];
		$aHandleJs = [];
		wp_register_style(
			WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5(App::get('dataConfig')['css']),
			App::get('dataConfig')['css'],
			[],
			WILOKE_WILOKEPOSTCATEGORIESAVENUE_VERSION);

		$aHandleCss[] = WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5(App::get('dataConfig')['css']);

		wp_register_script(
			WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5(App::get('dataConfig')['js']),
			App::get('dataConfig')['js'],
			['elementor-frontend'],
			WILOKE_WILOKEPOSTCATEGORIESAVENUE_VERSION,
			true
		);
		$aHandleJs[] = WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5(App::get('dataConfig')['js']);
		if (isset(App::get('dataConfig')['libs']['css']) && !empty($aLibCss = App::get('dataConfig')['libs']['css'])) {
			foreach ($aLibCss as $urlCss) {
				wp_register_style(
					WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5($urlCss),
					$urlCss,
					[], WILOKE_WILOKEPOSTCATEGORIESAVENUE_VERSION);
				$aHandleCss[] = WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5($urlCss);
			}
		}
		App::bind('handleCss', $aHandleCss);

		if (isset(App::get('dataConfig')['libs']['js']) && !empty($aLibJs = App::get('dataConfig')['libs']['js'])) {
			foreach ($aLibJs as $urlJs) {
				wp_register_script(
					WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5($urlJs),
					$urlJs,
					[],
					WILOKE_WILOKEPOSTCATEGORIESAVENUE_VERSION,
					true
				);
			}
			$aHandleJs[] = WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE . md5($urlJs);
		}
		App::bind('handleJs', $aHandleJs);
		wp_localize_script('jquery', 'WilokePostCategoriesAvenue', [
			'prefix' => WILOKE_WILOKEPOSTCATEGORIESAVENUE_NAMESPACE,
			'userID' => get_current_user_id(),
			'ajaxUrl' => admin_url('admin-ajax.php')
		]);
	}

	public function registerAddon($oWidgetManager)
	{
		$oWidgetManager->register(new PluginAddon());
	}

	public function registerControls(Controls_Manager $oControlManager)
	{
		
		
		
		
	}
}