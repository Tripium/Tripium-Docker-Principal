<?php

namespace WooKit\MailServices\iContact\src\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\Middleware\Middlewares\IMiddleware;
use WooKit\MailServices\iContact\src\Shared\IcontactConnection;

class IsValidListIDMiddleware implements IMiddleware{

	public function validation( array $aAdditional = [] ): array {
		$listID      = $aAdditional['listID'] ?? '';
		$appID       = $aAdditional['appID'] ?? '';
		$appUsername = $aAdditional['appUsername'] ?? '';
		$appPassword = $aAdditional['appPassword'] ?? '';
		if( !empty($listID) ) {
			return MessageFactory::factory()
				->response(IcontactConnection::connect($appID, $appUsername, $appPassword)
					->getListInfo($listID)
				);
		}
		return MessageFactory::factory()->error(esc_html__('Oops! Look like your list ID is empty. Please check again.',
			'wookit'),
			400
		);
	}
}
