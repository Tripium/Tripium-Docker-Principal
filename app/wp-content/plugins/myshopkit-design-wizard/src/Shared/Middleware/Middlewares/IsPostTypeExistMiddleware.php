<?php

namespace MyshopKitDesignWizard\Shared\Middleware\Middlewares;

use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;

class IsPostTypeExistMiddleware implements IMiddleware
{

	/**
	 * @throws Exception
	 */
	public function validation(array $aAdditional = []): array
	{
		$postID = $aAdditional['postID'] ?? '';
		$postType = $aAdditional['postType'] ?? '';
		if (empty($postID)) {
			throw new Exception('Sorry, the id is required', 400);
		}
		if (get_post_field('post_type', $postID) != $postType) {
			throw new Exception(sprintf(esc_html__('Unfortunately, this item is not a %s',
				'myshopkit-design-wizard'), $postType));
		}
		return MessageFactory::factory()->success('Passed');
	}
}