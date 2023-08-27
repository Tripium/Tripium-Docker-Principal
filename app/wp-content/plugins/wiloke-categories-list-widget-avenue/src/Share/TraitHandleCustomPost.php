<?php

namespace WilokePostCategoriesAvenue\Share;

trait TraitHandleCustomPost
{
	public string $wilCustomPostKey = 'wil-custom-post';

	public function updateFieldsData(int $postID, array $aData)
	{
		return update_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomPostKey), json_encode($aData));
	}

	public function getFieldsData(int $postID): array
	{
		return json_decode(get_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomPostKey), true), true) ?? [];
	}

	public function deleteFieldsData(int $postID)
	{
		return update_post_meta($postID, AutoPrefix::namePrefix($this->wilCustomPostKey),'');
	}
}