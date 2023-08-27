<?php

namespace MyshopKitDesignWizard\Shared;

trait TraitPageDetector
{
	private function isMyShopKitArea(): bool
	{
		if (isset($_GET['page']) && strpos($_GET['page'], 'mskdw') != -1) {
			return true;
		}

		return false;
	}
}
