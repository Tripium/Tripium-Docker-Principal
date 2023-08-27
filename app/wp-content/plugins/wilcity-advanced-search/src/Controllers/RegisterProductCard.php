<?php


namespace WilcityAdvancedSearch\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Register\RegisterMenu\RegisterListingScripts;

class RegisterProductCard extends RegisterListingScripts
{
	public function __construct()
	{
		add_filter(
			'wilcity/filter/wilcity-advanced-search/Controllers/search-form-settings',
			[$this, 'printShopCard']
		);
	}

	public function printShopCard($aFields)
	{
		$aConfig = include WILCITY_ADVANCED_CONFIG_DIR . 'product-card.php';

		$aListingCard['body'] = $this->getUsedBodyListingCard();
		$aListingCard['footer'] = GetSettings::getOptions(
			General::getSingleListingSettingKey('footer_card', $this->postType)
		);
		if (!is_array($aListingCard['footer'])) {
			$aListingCard['footer'] = [
				'taxonomy' => 'product_cat'
			];
		}

		$aListingCard['header'] = GetSettings::getOptions(
			General::getSingleListingSettingKey('header_card', $this->postType)
		);
		if (!is_array($aListingCard['header'])) {
			$aListingCard['header'] = [
				'btnAction' => 'call_us'
			];
		}

		$aFields['listingCards'] = [
			'value'      => $aListingCard,
			'settings'   => [
				'header' => [
					'options' => $aConfig['aButtonInfoOptions']
				],
				'body'   => [
					'fields'     => $aConfig['bodyFields'],
					'fieldTypes' => $aConfig['bodyFields']
				]
			],
			'ajaxAction' => 'wilcity_save_listing_card'
		];

		return $aFields;
	}
}
