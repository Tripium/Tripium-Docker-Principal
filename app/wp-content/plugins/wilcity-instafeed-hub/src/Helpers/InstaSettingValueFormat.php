<?php


namespace WilokeInstagramFeedhub\Helpers;


class InstaSettingValueFormat
{
	protected static $aDefineValueType
		= [
			'id'           => 'string',
			'slot_data_id' => 'string',
		];

	public static function correctValueType($val, $key)
	{
		if (isset(self::$aDefineValueType[$key])) {
			settype($val, self::$aDefineValueType[$key]);
		} else {
			if (is_numeric($val)) {
				$val = floatval($val);
			}
		}

		return $val;
	}
}
