<?php

namespace MyshopKitDesignWizard\Projects\Controllers;



use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;

class RegistrationController
{
	public function __construct()
	{
		add_action('cmb2_admin_init', [$this, 'registerBox']);
		add_action('init', [$this, 'registerManual'], 0);
	}

	public function registerManual()
	{
		$aConfig = include plugin_dir_path(__FILE__) . "../Configs/PostType.php";
		$aPostType = [];
		foreach ($aConfig as $key => $aItem) {
			register_post_type(
				$aItem['postType'],
				$aItem
			);
			$aPostType[] = $aItem['postType'];
		}

		$aTaxonomies = include plugin_dir_path(__FILE__) . "../Configs/Taxonomies.php";

		if (!empty($aTaxonomies)) {
			foreach ($aTaxonomies as $taxonomyName => $aData) {
				register_taxonomy(
					AutoPrefix::namePrefix($taxonomyName),
					$aPostType,
					$aData);
			}
		}
	}

	public function registerBox()
	{
		$aMetaData = include plugin_dir_path(__FILE__). '../Configs/PostMeta.php';

		foreach ($aMetaData as $aSection) {
			$aFields = $aSection['fields'];
			unset($aSection['fields']);
			$oCmb = new_cmb2_box($aSection);
			foreach ($aFields as $aField) {
				$aField['id'] = AutoPrefix::namePrefix($aField['id']);
				$oCmb->add_field($aField);
			}
		}
	}
}