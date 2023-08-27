<?php

namespace WilokePostCategoriesAvenue\Share;

trait TraitHandleCustomProduct
{
	public string $wilCustomProductKey = 'wil-custom-product';

	public function updateFieldsProductData(int $postID, array $aData)
	{
		return update_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomProductKey), json_encode($aData));
	}

	public function getFieldsProductData(int $postID): array
	{
		return json_decode(get_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomProductKey), true), true) ?? [];
	}

	public function deleteFieldsProductData(int $postID)
	{
		return update_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomProductKey), '');
	}
}