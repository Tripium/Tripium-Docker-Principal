<?php
return apply_filters(MYSHOPKIT_DW_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\middlewares',
	[
		'IsUserLoggedIn'            => 'MyshopKitDesignWizard\Shared\Middleware\Middlewares\IsUserLoggedIn',
		'IsPostExistMiddleware'     => 'MyshopKitDesignWizard\Shared\Middleware\Middlewares\IsPostExistMiddleware',
		'IsPostAuthorMiddleware'    => 'MyshopKitDesignWizard\Shared\Middleware\Middlewares\IsPostAuthorMiddleware',
		'IsPostTypeExistMiddleware' => 'MyshopKitDesignWizard\Shared\Middleware\Middlewares\IsPostTypeExistMiddleware'
	]
);
