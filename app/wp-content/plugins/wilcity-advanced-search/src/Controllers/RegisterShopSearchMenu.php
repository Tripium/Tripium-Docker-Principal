<?php


namespace WilcityAdvancedSearch\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Register\ListingToolsGeneralConfig;

class RegisterShopSearchMenu
{
	use ListingToolsGeneralConfig;

	public $slug                   = 'product_settings';
	public $postType               = 'product';
	public $aSearchUsedFields      = [];
	public $aAvailableSearchFields = [];
	public $aAllFields             = [];

	public function __construct()
	{
		add_action('wilcity/wiloke-listing-tools/register-menu', [$this, 'registerMenu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
	}

	protected function getAvailableFields()
	{
		$this->aAvailableSearchFields = $this->aAllFields;

		return $this;
	}

	protected function getUsedFields(): RegisterShopSearchMenu
	{
		$this->aSearchUsedFields = GetSettings::getOptions(General::getSearchFieldsKey($this->postType));
		return $this;
	}

	protected function setupFields()
	{
		$this->aAllFields = include WILCITY_ADVANCED_SEARCHFORM_DIR . 'config/search-fields.php';
		$this->getUsedFields()->getAvailableFields();
	}

	public function enqueueScripts($hook)
	{
		if (strpos($hook, $this->slug) !== false) {
			$this->requiredScripts();
			$this->designListingSettings();
			$this->generalScripts();
			$this->setupFields();

			wp_localize_script(
				'listing-settings',
				'WILOKE_LISTING_TOOLS',
				apply_filters(
					'wilcity/filter/wilcity-advanced-search/Controllers/search-form-settings',
					[
						'PRODUCT_ASSETS_URL' => WILOKE_LISTING_TOOL_URL . 'admin/source/js/',
						'postType'           => $this->postType,
						'restURL'            => rest_url('wiloke/v2/'),
						'aSearchForm'        => [
							'aUsedFields'      => $this->aSearchUsedFields,
							'aAllFields'       => $this->aAllFields,
							'aAvailableFields' => !is_array($this->aAvailableSearchFields) ? [] :
								array_values($this->aAvailableSearchFields),
							'ajaxAction'       => 'wilcity_search_fields',
							'toggle'           => GetSettings::getOptions(General::getSearchFieldToggleKey
							($this->postType), true, false, 'enable'),
							'hasToggle'        => 'yes'
						]
					]
				)
			);
		}
	}

	public function registerMenu()
	{
		add_submenu_page(
			$this->parentSlug,
			'Shop Settings',
			'Shop Settings',
			'edit_theme_options',
			$this->slug,
			[$this, 'registerSettings']
		);
	}

	public function registerSettings()
	{
		include WILCITY_ADVANCED_SEARCHFORM_DIR . 'views/listing-settings/index.php';
	}
}
