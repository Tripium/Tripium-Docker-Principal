<?php
return apply_filters(WOOKIT_HOOK_PREFIX . 'Filter\Shared\Middleware\Configs\MyShopKitMiddleware',
    [
        'IsUserLoggedIn'                    => 'WooKit\Shared\Middleware\Middlewares\IsUserLoggedIn',
        'IsShopLoggedInLowLevelCheck'       => 'WooKit\Shared\Middleware\Middlewares\IsShopLoggedInLowLevelCheckMiddleware',
        'IsShopLoggedInHighLevelCheck'      => 'WooKit\Shared\Middleware\Middlewares\IsShopLoggedInHighLevelCheckMiddleware',
        'IsValidEmail'                      => 'WooKit\Shared\Middleware\Middlewares\IsValidEmailMiddleware',
        'IsReachMaximumPlanAllow'           => 'WooKit\Plans\Middlewares\IsReachMaximumPlanAllowMiddleware',
        'IsDisableMyShopKitBrandMiddleware' => 'WooKit\Plans\Middlewares\IsDisableMyShopKitBrandMiddleware',
        'IsCampaignExist'                   => 'WooKit\Shared\Middleware\Middlewares\IsCampaignExistMiddleware',
        'IsCampaignTypeExist'               => 'WooKit\Shared\Middleware\Middlewares\IsCampaignTypeExistMiddleware',
        'IsWoocommerceActive'               => 'WooKit\Shared\Middleware\Middlewares\IsWoocommerceActiveMiddleware',
    ]
);
