<?php

namespace WilcityVR\Controllers;

use WilcityVR\Helpers\App;

class AddListingController
{
	public function __construct()
	{
		add_filter(
			'wilcity/filter/wiloke-listing-tools/configs/settings',
			[$this, 'addAddListingSettings'],
			10
		);

//		add_filter(
//			'wiloke-listing-tools/filter/app/Controllers/Validation/surePassConditional/cover_image',
//			[$this, 'modifyCoverImageValidation'],
//			10,
//			4
//		);
	}

	public function addAddListingSettings($aFields)
	{
		$aFields['header']['fieldGroups'][] = App::get('addlisting')['field'];
		return $aFields;
	}

	// If the cover image field is required and it's empty, but the vr_src is not empty, it still be corrected
	public function modifyCoverImageValidation($isValid, $sectionKey, $aFieldInfo, $aData)
	{
		if ($sectionKey !== 'header' || $isValid) {
			return $isValid;
		}

		if (isset($aFieldInfo['isRequired']) && $aFieldInfo['isRequired'] == 'yes') {
			if (isset($aData[$sectionKey]) && isset($aData[$sectionKey]['vr_src']) &&
				!empty($aData[$sectionKey]['vr_src'])) {
				$isValid = true;
			}
		}

		return $isValid;
	}
}
