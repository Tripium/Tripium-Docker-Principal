<?php


namespace MyshopKitDesignWizard\Shared\Middleware\Middlewares;


use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;
use WP_User;

class IsUserLoggedIn implements IMiddleware
{

	public function validation(array $aAdditional = []): array
	{
		if (isset($aAdditional['authorization'])) {
			$authorization = str_replace('Bearer ', '', $aAdditional['authorization']);
			$aInfo = explode(':', base64_decode($authorization));
			$userLogin = $aInfo[0] ?? '';
			$oUser = get_user_by('login', $userLogin);
			if ($oUser instanceof WP_User) {
				set_current_user($oUser->ID);
			}
		}
		if (!is_user_logged_in()) {
			return MessageFactory::factory()->error(
				esc_html__('Sorry, The account is not permission', 'myshopkit-design-wizard'),
				400
			);
		}

		return MessageFactory::factory()->success('Passed');
	}
}
