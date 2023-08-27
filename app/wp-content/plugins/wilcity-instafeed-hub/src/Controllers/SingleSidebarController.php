<?php

namespace WilokeInstagramFeedhub\Controllers;

use WilokeInstagramFeedhub\Helpers\App;

class SingleSidebarController
{
	public function __construct()
	{
		add_filter('wilcity/wiloke-listing-tools/filter/sidebar-items', [$this, 'addSidebarItem']);
		add_filter('wilcity/wiloke-listing-tools/filter/sidebar-machine', [$this, 'addSidebarMachine']);
		add_filter('wilcity/wiloke-listing-tools/filter/sidebar-fields', [$this, 'addSidebarSettings']);
	}

	public function addSidebarSettings($aItems)
	{
		$aItems['sections'] = array_merge($aItems['sections'], App::get('configs/sidebar')['settings']);
		return $aItems;
	}

	public function addSidebarItem($aItems)
	{
		$aItems['instafeedhub'] = App::get('configs/sidebar')['default'];

		return $aItems;
	}

	public function addSidebarMachine($aShortcodes)
	{
		$aShortcodes['instafeedhub'] = 'wilcity_sidebar_instafeedhub';

		return $aShortcodes;
	}
}
