<?php

namespace MyshopKitDesignWizard\Shared;

class Option
{
	public static function isImported() {
		return get_option(MYSHOPKIT_DW_PREFIX.'_imported_data');
	}

	public static function updateImported($status) {
		update_option(MYSHOPKIT_DW_PREFIX.'_imported_data', $status);
	}
}
