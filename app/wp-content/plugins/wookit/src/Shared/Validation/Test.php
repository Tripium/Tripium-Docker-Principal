<?php

namespace WooKit\Shared\Validation;

use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use Webmozart\Assert\Assert;

class Test
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouter']);
    }

    public function registerRouter()
    {
        register_rest_route(WOOKIT_REST, 'test-validation', [
            [
                'methods'  => 'GET',
                'callback' => [$this, 'responseData'],
                'permission_callback' => '__return_true'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function responseData()
    {
        return MessageFactory::factory('rest')->response(
            Validation::make(
                [
                    'timeline' => [
                        [
                            'from'    => '123',
                            'to'      => '456',
                            'summary' => 1232
                        ],
                        [
                            'from'    => '123',
                            'to'      => '456',
                            'summary' => '456'
                        ]
                    ],
                    'id'       => '123',
                    'username' => 'Wiloke',
                    'status'   => 'error'
                ],
                [
                    'timeline' => [
                        Rule::validArrayChild([
                            Rule::allKeyExistsInArray(['from', 'to', 'summary']),
                            Rule::validArrayValue([
                                'from' => ['string'],
                                'to'   => ['string'],
                                'summary'=> ['string']
                            ])
                        ]),
                    ],
                    'id'       => [
                        'string'
                    ],
                    'username' => [
                        'required'
                    ],
                    'status'   => [
                        Rule::inArray(['success', 'error'])
                    ]
                ]
            )
        );
    }
}
