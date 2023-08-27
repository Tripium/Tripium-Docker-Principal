<?php

namespace WilcityAddListingOnApp\Controllers;

class ThemeOptionsController
{
	public function __construct()
	{
		add_filter('wilcity/filter/wilcity-mobile-app/configs/themeoptions', [$this, 'addThemeOptions']);
	}

	public function addThemeOptions($aOptions)
	{
		$aOptions['fields'] = array_merge(
			$aOptions['fields'],
			[
				[
					'id'     => 'app_add_listing',
					'title'  => 'AddListing Settings',
					'type'   => 'section',
					'indent' => true
				],
				[
					'id'      => 'app_toggle_add_listing',
					'type'    => 'select',
					'title'   => 'Toggle Add Listing',
					'default' => 'disable',
					'options' => [
						'enable'  => 'Enable',
						'disable' => 'Disable'
					]
				],
				[
					'id'     => 'app_add_listing_section_close',
					'title'  => '',
					'type'   => 'section',
					'indent' => false
				]
			]
		);

		return $aOptions;
	}
}
