<?php

namespace WilokePostCategoriesAvenue\Share;

class HandleQueryData
{
	private static $oSelf = null;
	use TraitHandleCustomPost, TraitHandleQuery, TraitHandleCustomProduct;

	public static function initSingleClass(): ?HandleQueryData
	{
		if (self::$oSelf == null) {
			self::$oSelf = new self();
			return self::$oSelf;
		}
		return self::$oSelf;
	}

	public function queryDataProduct($aSetting)
	{
		return self::$oSelf->commonParseArgs($aSetting)->query('product');
	}

	public function queryDataCatProduct($aSetting)
	{
		return self::$oSelf->commonParseArgs($aSetting)->query('catProducts');
	}

	public function queryDataPost($aSetting)
	{
		return self::$oSelf->commonParseArgs($aSetting)->query();
	}

	public function queryDataCatPost($aSetting)
	{
		return self::$oSelf->commonParseArgs($aSetting)->query('catPosts');
	}
}