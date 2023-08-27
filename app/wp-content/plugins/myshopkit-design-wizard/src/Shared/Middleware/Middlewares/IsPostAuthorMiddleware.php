<?php

namespace MyshopKitDesignWizard\Shared\Middleware\Middlewares;

use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;

class IsPostAuthorMiddleware implements IMiddleware
{
	/**
	 * @throws Exception
	 */
	public function validation(array $aAdditional = []): array
	{
		$postID = $aAdditional['postID'] ?? '';
		$userID = $aAdditional['userID'] ?? '';
		if (empty($postID)) {
			throw new Exception('Sorry, the post id is required', 400);
		}
		if (empty($userID)) {
			throw new Exception('Sorry, the user id is required', 400);
		}
		if (get_post_field('post_author', $postID) != $userID) {
			throw new Exception(esc_html__('Unfortunately, You were not post author',
				'myshopkit-design-wizard'));
		}
		return MessageFactory::factory()->success('Passed');
	}
}