<?php


namespace MyshopKitDesignWizard\Projects\Services\Post;


use Exception;


use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeAssertion;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeSanitizeCallback;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Shared\Post\IService;

class CreatePostService extends PostService implements IService
{
	use TraitDefinePostFields;
	use TraitMaybeAssertion;
	use TraitMaybeSanitizeCallback;


	/**
	 * @return array
	 */
	public function performSaveData(): array
	{
		try {
			$this->validateFields();
			$aData = $this->aData;
			unset($aData['ID']);
			$id = wp_insert_post($aData);
			if (is_wp_error($id)) {
				return MessageFactory::factory()->error($id->get_error_message(), $id->get_error_code());
			}
			if (!empty($aData['thumbnail'])) {
				$attachmentId = json_decode($aData['thumbnail'], true)['id'] ?? 0;
				set_post_thumbnail($id, $attachmentId);
			}
			if (!empty($aData['taxonomies'])) {
				$aTags = array_map(function ($id) {
					$tagId = (int)trim($id);
					$oTag = get_term($tagId, AutoPrefix::namePrefix('post_tag'));
					return $oTag->name;
				}, json_decode($aData['taxonomies'], true)['tags'] ?? []);
				if (!empty($aTags)) {
					wp_set_object_terms($id, $aTags, AutoPrefix::namePrefix('post_tag'));
				}
			}
			return MessageFactory::factory()->success(
				esc_html__('Congrats! The item has created successfully', 'myshopkit-design-wizard'),
				[
					'id' => $id
				]
			);
		}
		catch (Exception $oException) {
			return MessageFactory::factory()->error($oException->getMessage(), $oException->getCode());
		}
	}

	/**
	 * @throws Exception
	 */
	public function validateFields(): IService
	{
		foreach ($this->defineFields() as $friendlyKey => $aField) {
			if (isset($aField['isReadOnly'])) {
				$this->aData[$aField['key']] = $aField['value'];
			} else {
				$value = '';
				if (isset($this->aRawData[$friendlyKey])) {
					$value = $this->aRawData[$friendlyKey];
				} else {
					if (isset($aField['value'])) {
						$value = $aField['value'];
					}
				}
				$aAssertionResponse = $this->maybeAssert($aField, $value);
				if ($aAssertionResponse['status'] === 'error') {
					throw new Exception($aAssertionResponse['message']);
				}
				$this->aData[$aField['key']] = $this->maybeSanitizeCallback($aField, $value);
			}
		}
		return $this;
	}

	public function setRawData(array $aRawData): IService
	{
		$this->aRawData = $aRawData;
		return $this;
	}
}
