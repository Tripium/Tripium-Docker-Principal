<?php


namespace MyshopKitDesignWizard\Projects\Services\Post;



use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Shared\Post\IDeleteUpdateService;
use MyshopKitDesignWizard\Shared\Post\IService;
use MyshopKitDesignWizard\Shared\Post\TraitIsPostAuthor;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeAssertion;
use MyshopKitDesignWizard\Shared\Post\TraitMaybeSanitizeCallback;

class UpdatePostService extends PostService implements IService, IDeleteUpdateService {
	use TraitDefinePostFields;
	use TraitMaybeAssertion;
	use TraitMaybeSanitizeCallback;
	use TraitIsPostAuthor;

	private $postID;

	public function setID( $id ): self
    {
        $this->postID = $id;

        return $this;
    }

	/**
	 * @throws Exception
	 */
	public function validateFields(): IService {
		if ( empty( $this->postID ) ) {
			throw new \Exception( esc_html__( 'The ID is required.', 'myShopkit-design-wizard' ) );
		}

		$this->isPostAuthor( $this->postID );
		foreach ( $this->defineFields() as $friendlyKey => $aField ) {
			if ( isset( $aField['isReadOnly'] ) || ! isset( $this->aRawData[ $friendlyKey ] ) ||
			     ! isset( $this->aRawData[ $friendlyKey ] ) ) {
				continue;
			} else {
				$value = $this->aRawData[ $friendlyKey ];
				$aAssertionResponse = $this->maybeAssert( $aField, $value );
				if ( $aAssertionResponse['status'] === 'error' ) {
					throw new \Exception( $aAssertionResponse['message'] );
				}

				$this->aData[ $aField['key'] ] = $this->maybeSanitizeCallback( $aField, $value );
			}
		}

		$this->aData['ID'] = $this->postID;

		return $this;
	}

	public function performSaveData(): array {
		try {
		    $this->validateFields();
			$id = wp_update_post( $this->aData );
			if ( is_wp_error( $id ) ) {
				return MessageFactory::factory()->error( $id->get_error_message(), $id->get_error_code() );
			}

			return MessageFactory::factory()->success(
				esc_html__( 'Congrats! The project has been updated successfully', 'myShopkit-design-wizard' ),
				[
					'id' => $id
				]
			);
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()->error( $oException->getMessage(), $oException->getCode() );
		}
	}

	/**
	 * @param array $aRawData
	 *
	 * @return IService
	 */
	public function setRawData( array $aRawData ): IService {
		$this->aRawData = $aRawData;

		return $this;
	}
}
