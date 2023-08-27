<?php


namespace MyshopKitDesignWizard\Projects\Services\PostMeta;


use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Shared\Post\IDeleteUpdateService;
use MyshopKitDesignWizard\Shared\Post\IService;
use MyshopKitDesignWizard\Shared\Post\TraitIsPostAuthor;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeAssertion;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeSanitizeCallback;

class PostMetaService implements IService, IDeleteUpdateService {
	protected array $aRawData = [];
	protected array $aData    = [];
	protected       $postID;
	private bool    $isUpdate = false;

	use TraitDefinePostMetaFields;
	use TraitMaybeAssertion;
	use TraitMaybeSanitizeCallback;
	use TraitIsPostAuthor;

	public function setID( $id ): self {
		$this->postID = $id;

		return $this;
	}

	public function setRawData( array $aRawData ): IService {
		$this->aRawData = $aRawData;

		return $this;
	}

	public function performSaveData(): array {
		try {
			$this->validateFields();
			foreach ( $this->aData as $metaKey => $metaValue ) {
				update_post_meta( $this->postID, AutoPrefix::namePrefix( $metaKey ), $metaValue );
			}

			return MessageFactory::factory()->success(
				esc_html__( 'The data have been updated successfully.', 'myShopkit-design-wizard' )
			);
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()->error( $oException->getMessage(), $oException->getCode() );
		}
	}

	protected function setIsUpdate( $status ): PostMetaService {
		$this->isUpdate = $status;

		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function validateFields(): IService {
		if ( ! $this->postID ) {
			throw new \Exception( esc_html__( 'You must set the post id.', 'myShopkit-design-wizard' ) );
		}

		$this->isPostAuthor( $this->postID );
		foreach ( $this->defineFields() as $friendlyKey => $aField ) {
			if ( isset( $aField['isReadOnly'] ) ) {
				if ( ! $this->isUpdate ) {
					$this->aData[ $aField['key'] ] = $aField['value'];
				}
			} else {
				if ( $this->isUpdate ) {
					if ( ! isset( $this->aRawData[ $friendlyKey ] ) ) {
						continue;
					}
				}

				$value = $this->aRawData[ $friendlyKey ] ?? '';
				$aAssertionResponse = $this->maybeAssert( $aField, $value );
				if ( $aAssertionResponse['status'] === 'error' ) {
					throw new \Exception( $aAssertionResponse['message'] );
				}

				$this->aData[ $aField['key'] ] = $this->maybeSanitizeCallback( $aField, $value );
			}
		}

		return $this;
	}
}
