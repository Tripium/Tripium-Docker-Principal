<?php

namespace WooKit\Shared\Middleware\Middlewares;

use WooKit\Illuminate\Message\MessageFactory;

class IsWoocommerceActiveMiddleware implements IMiddleware
{
	public function validation(array $aAdditional = []): array
	{
		include(ABSPATH . 'wp-admin/includes/plugin.php');
		if (is_plugin_active('woocommerce/woocommerce.php')) {
			return MessageFactory::factory()->success('Passed');
		} else {
			return MessageFactory::factory()->error(esc_html__('Sorry,Woocommerce plugin is required',
				'wookit'), 401);
		}
	}
}
