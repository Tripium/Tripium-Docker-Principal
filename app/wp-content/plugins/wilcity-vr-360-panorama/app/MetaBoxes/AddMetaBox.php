<?php

namespace WilcityVR\MetaBoxes;

use WilcityVR\Helpers\App;

class AddMetaBox
{
	public function __construct()
	{
		add_filter('wilcity/general-settings/fields', [$this, 'addVRSourceToHeaderBox']);
	}

	public function addVRSourceToHeaderBox($aFields)
	{
		$aFields[] = App::get('metabox');
		return $aFields;
	}
}