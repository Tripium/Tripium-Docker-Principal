<?php


namespace MyshopKitDesignWizard\Projects\Services\Post;


use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use MyshopKitDesignWizard\Shared\Post\IDeleteUpdateService;
use MyshopKitDesignWizard\Shared\Post\TraitIsPostAuthor;
use MyshopKitDesignWizard\Shared\Post\TraitIsPostType;
use WP_Post;

class DeletePostService implements IDeleteUpdateService
{
	use TraitIsPostAuthor;
	use TraitIsPostType;

	private string $postID;

	public function setID($id): self
	{
		$this->postID = $id;

		return $this;
	}


	public function delete(): array
	{
		try {

			$oPost = wp_delete_post($this->postID,true);

			if ($oPost instanceof WP_Post) {
				return MessageFactory::factory()->success(esc_html__('Congrats, the project has been deleted.',
					'myShopkit-design-wizard'), [
					'id' => $oPost->ID
				]);
			}

			return MessageFactory::factory()->error(esc_html__('Sorry, We could not delete this project.',
				'myShopkit-design-wizard'), 400);
		}
		catch (Exception $oException) {
			return MessageFactory::factory()->error(
				$oException->getMessage(),
				$oException->getCode()
			);
		}

	}
}
