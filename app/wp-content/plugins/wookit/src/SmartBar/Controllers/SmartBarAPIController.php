<?php

namespace WooKit\SmartBar\Controllers;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Message\MessageDefinition;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\Shared\MultiLanguage\MultiLanguage;
use WooKit\Shared\Post\TraitHandleGetDuplicate;
use WooKit\Shared\Post\TraitHandleGetShowOnPageCampaign;
use WooKit\Shared\PostMeta\TraitShowOnPageHandler;
use WooKit\SmartBar\Services\Post\CreatePostService;
use WooKit\SmartBar\Services\Post\DeletePostService;
use WooKit\SmartBar\Services\Post\PostSkeletonService;
use WooKit\SmartBar\Services\Post\UpdatePostService;
use WooKit\SmartBar\Services\PostMeta\AddPostMetaService;
use WooKit\SmartBar\Services\PostMeta\UpdatePostMetaService;
use WP_REST_Request;

class SmartBarAPIController
{
    use TraitShowOnPageHandler;
    use TraitMainMiddleware, TraitHandleGetDuplicate, TraitHandleGetShowOnPageCampaign;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
        add_action('update_post_meta', [$this, 'maybeUpdateConfigAfterUpdateSmartBar'], 10, 4);
        add_action('add_post_metadata', [$this, 'maybeUpdateConfigAfterUpdateSmartBar'], 10, 4);
        add_action($this->getScheduleKey(), [$this, 'reUpdateSmartBarConfig']);
        add_filter(WOOKIT_PREFIX . 'Filter/General/Controllers/GeneralController/getCampaignsStatus',
            [$this, 'handleSmartbarIsEmpty'], 10, 2);
        add_filter(WOOKIT_PREFIX . 'Filter/PostScript/Controllers/PostScriptController/getCampaignsActive',
            [$this, 'getCampaignsActive']);
    }

    public function getCampaignsActive(array $aResponse): array
    {
        $aPopupResponse = (new SmartBarQueryService())->setRawArgs(
            [
                'status' => 'active',
                'limit'  => 50
            ]
        )->parseArgs()->query(new PostSkeletonService(), 'config,date,id,title');
        if (!empty($aPopupResponse['data']['items'])) {
            $aResponse['smartbar'] = $aPopupResponse['data']['items'];
        }
        return $aResponse;
    }

    public function maybeUpdateConfigAfterUpdateSmartBar($metaID, $postID, $metaKey, $metaValue)
    {
        $this->maybeUpdateConfigAfterUpdatePost($postID, $metaKey, 'smartbar');
    }

    public function reUpdateSmartBarConfig($postID)
    {
        $this->reUpdateConfig($postID, 'smartbar');
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'smartbars',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'createSmartBar'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSmartBars'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'delete-smartbars',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'deleteSmartBars'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'smartbars/(?P<id>(\d+))/focus-active',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateActiveForce'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'me/smartbars/publishing',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getMyPublishSmartBars'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'smartbars/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSmartBar'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateSmartBar'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'PATCH',
                    'callback'            => [$this, 'updateSmartBar'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteSmartBar'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function handleSmartbarIsEmpty($aResponse, $aData)
    {
        $aResponseSmartbar = (new SmartBarQueryService())->setRawArgs(
            array_merge(
                [
                    'limit' => 1
                ],
                []
            )
        )->parseArgs()->query(new PostSkeletonService(), 'title');

        $aResponse['hasSmartbar'] = !empty($aResponseSmartbar['data']['items']);
        return $aResponse;
    }

    public function updateActiveForce(WP_REST_Request $oRequest)
    {
        $postID = $oRequest->get_param('id');
        try {
            $aResponse = $this->processMiddleware([
                'IsUserLoggedIn',
                'IsCampaignExist',
                'IsCampaignTypeExist',
                //'IsDisableMyShopKitBrandMiddleware'
            ], [
                'postType' => AutoPrefix::namePrefix('smartbar'),
                'postID'   => $postID,
                'userID'   => get_current_user_id(),
                'config'   => get_post_meta($postID, AutoPrefix::namePrefix('config'), true)
            ]);

            if ($aResponse['status'] == 'error') {
                throw new Exception($aResponse['message'], $aResponse['code']);
            }

            $aResponseDuplicate = $this->getDuplicate(
                new SmartBarQueryService(),
                $this->getShowOnPageCampaign($postID),
                $postID
            );
            //kiểm tra xem có duplicate thì deactive
            if ($aResponseDuplicate['status'] == 'success') {
                $aID = $aResponseDuplicate['data']['aListIDs'];
                foreach ($aID as $key => $id) {
                    $aPostResponse = (new UpdatePostService())
                        ->setID($id)
                        ->setRawData(['status' => 'deactive'])
                        ->performSaveData();
                    if ($aPostResponse['status'] == 'error') {
                        $aListOfErrors[$id] = get_the_title($id);
                    } else {
                        $aListOfSuccess[$id] = get_the_title($id);
                    }
                }
            }
            //không có thì vẫn active campaign bắn lên
            $aPostResponse = (new UpdatePostService())
                ->setID($postID)
                ->setRawData(['status' => 'active'])
                ->performSaveData();

            if ($aPostResponse['status'] == 'error') {
                return MessageFactory::factory('rest')->success(
                    esc_html__('Oops! Something went error, We could not active the Campaign.', 'myshopkit'),
                    [
                        'deactivateIDs' => [$postID]
                    ]
                );
            }

            do_action(
                WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/updateActiveForce/Updated',
                [
                    'postID'        => $postID,
                    'deactivateIDs' => array_keys($aListOfSuccess)
                ]
            );

            if (empty($aListOfErrors)) {
                return MessageFactory::factory('rest')->success(
                    esc_html__('Congrats, the smartbart has been published.', 'wookit'),
                    [
                        'deactivateIDs' => array_keys($aListOfSuccess)
                    ]
                );
            }

            return MessageFactory::factory('rest')
                ->success(
                    sprintf(
                        'The following smartbar have been updated: %s. We could not update the following smartbars: %s',
                        implode(',', $aListOfSuccess), implode(',', $aListOfErrors)
                    ),
                    [
                        'deactivateIDs' => array_keys($aListOfSuccess)
                    ]
                );
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
        }
    }

    public function getMyPublishSmartBars(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $locale = $oRequest->get_param('locale');
        $showOnPages = $oRequest->get_param('showOnPage');
        $showOnPages = empty($showOnPages) ? 'all' : $showOnPages;

        $aResponse = (new SmartBarQueryService())->setRawArgs(
            array_merge(
                [
                    'status'     => 'active',
                    'showOnPage' => $showOnPages
                ],
                [
                    'author' => get_current_user_id()
                ]
            )
        )->parseArgs()->query(new PostSkeletonService, 'id,title');

        if (empty($aResponse['data']['items'])) {
            return MessageFactory::factory()->success(
                'We found no publish campaigns',
                []
            );
        }

        return MessageFactory::factory()->success(
            sprintf(
                MultiLanguage::setLanguage($locale)->getMessage('duplicateCampaign'),
                $aResponse['data']['items'][0]['title']
            ),
            $aResponse['data']['items'][0]
        );
    }

    public function deleteSmartBar(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');

        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/deleteSmartBar/BeforeDelete',
            [
                'ID' => $postID
            ]
        );

        if (isset($aVerifyBeforeDelete['status']) && $aVerifyBeforeDelete['status'] == 'error') {
            return MessageFactory::factory('rest')->response($aVerifyBeforeDelete);
        }

        $aPostResponse = (new DeletePostService())->setID($postID)->delete();
        if ($aPostResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aPostResponse['message'], $aPostResponse['code']);
        }

        do_action(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/deleteSmartBar/Deleted',
            [
                'postID' => $aPostResponse['data']['id'],
                'data'   => $oRequest->get_params()
            ]
        );

        return MessageFactory::factory('rest')->success(
            $aPostResponse['message'],
            [
                'id' => $aPostResponse['data']['id']
            ]
        );
    }

    public function updateSmartBar(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');
        $aConfig = $oRequest->get_param('config');
        $aData = [
            'upgrade'   => [
                'isUpgrade' => false,
                'message'   => ''
            ],
            'duplicate' => [
                'isDuplicate' => false,
                'message'     => ''
            ]
        ];

        if (empty($userID = get_current_user_id())) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $status = $oRequest->get_param('status');
        if (empty($status)) {
            $status = get_post_field('post_status', $postID) == 'publish' ? 'active' : 'deactive';
        }

        if ($status == 'active') {
            $aConfig = empty($aConfig) ?
                get_post_meta($postID, AutoPrefix::namePrefix('config'), true) :
                $aConfig;

            $aResponseMiddleware = $this->processMiddleware([
                'IsUserLoggedIn',
                'IsCampaignTypeExist'
            ], [
                'postType' => AutoPrefix::namePrefix('smartbar'),
                'userID'   => $userID,
                'locale'   => $oRequest->get_param('locale'),
                'config'   => $aConfig
            ]);

            if ($aResponseMiddleware['status'] == 'error') {
                $oRequest->set_param('status', 'deactive');
                $aData['upgrade'] = [
                    'isUpgrade' => ($aResponseMiddleware['status'] == 'error'),
                    'message'   => $aResponseMiddleware['message']
                ];
            } else {
                $showOnPage = (isset($aConfig['targeting']['showOnPage']) &&
                    !empty($aConfig['targeting']['showOnPage']))
                    ? $aConfig['targeting']['showOnPage'] : $this->getShowOnPageCampaign($postID);
                $aResponse = $this->getDuplicate(
                    new SmartBarQueryService(),
                    $showOnPage,
                    $postID
                );
                if ($aResponse['status'] == 'success') {
                    $aData['duplicate'] = [
                        'isDuplicate' => true,
                        'ids'         => implode(',', $aResponse['data']['aListIDs']),
                        'message'     => sprintf('We found **%s** campaigns are running on the same page and it will CAUSE CONFLICT PROBLEM with this campaign. Do you still disable the previous Campaigns?',
                            implode(',', $aResponse['data']['aListTitles']))
                    ];
                    $oRequest->set_param('status', 'deactive');
                }
            }
        }
        $aPostResponse = (new UpdatePostService())
            ->setID($postID)
            ->setRawData($oRequest->get_params())
            ->performSaveData();

        if ($aPostResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aPostResponse['message'], $aPostResponse['code']);
        }

        if ($aPostResponse['status'] == 'success') {
            $aResponse = (new UpdatePostMetaService())
                ->setID($aPostResponse['data']['id'])
                ->updatePostMeta($oRequest->get_params());

            if ($aResponse['status'] == 'error') {
                return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
            }
        }

        do_action(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/updateSmartBar/Updated',
            [
                'postID' => $postID,
                'data'   => $oRequest->get_params()
            ]
        );

        return MessageFactory::factory('rest')
            ->success($aPostResponse['message'],
                array_merge($aData,
                    [
                        'id'   => (string)$aPostResponse['data']['id'],
                        'date' => (string)strtotime(get_the_modified_date('Y-m-d H:i:s', $aPostResponse['data']['id']))
                    ])
            );
    }

    public function getSmartBar(WP_REST_Request $oRequest)
    {
        $aParams = $oRequest->get_params();
        $pluck = $aParams['pluck'] ?? '';
        unset($aParams['pluck']);

        $aResponse = (new SmartBarQueryService())->setRawArgs($aParams)->parseArgs()
            ->query(new PostSkeletonService(), $pluck, true);

        if ($aResponse['status'] == 'success') {
            return MessageFactory::factory('rest')->success($aResponse['message'], $aResponse['data']);
        } else {
            return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
        }
    }

    public function deleteSmartBars(WP_REST_Request $oRequest)
    {

        if (empty($oRequest->get_param('ids'))) {
            return MessageFactory::factory('rest')->error(
                esc_html__('Please provide 1 smartbar at least', 'wookit'),
                400
            );
        }
        $aPostIDs = explode(',', $oRequest->get_param('ids'));
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }
        $aListOfErrors = [];
        $aListOfSuccess = [];

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/deleteSmartBars/BeforeDelete',
            [
                'IDs' => $aPostIDs
            ]
        );
        if ((isset($aVerifyBeforeDelete['status'])) && $aVerifyBeforeDelete['status'] == 'error') {
            return MessageFactory::factory('rest')->response($aVerifyBeforeDelete);
        }

        $aPostIDs = $aVerifyBeforeDelete['IDs'];
        if (isset($aVerifyBeforeDelete['data']['errorCampaignIDs']) &&
            !empty($aVerifyBeforeDelete['data']['errorCampaignIDs'])) {
            $aListOfErrors = $aVerifyBeforeDelete['data']['errorCampaignIDs'];
        }

        $oDeletePostServices = new DeletePostService();

        foreach ($aPostIDs as $postID) {
            $aDeleteResponse = $oDeletePostServices->setID(trim($postID))->delete();
            if ($aDeleteResponse['status'] === 'error') {
                $aListOfErrors[] = $postID;
            } else {
                $aListOfSuccess[] = $postID;
            }
        }

        if (empty($aListOfErrors)) {
            return MessageFactory::factory('rest')->success(
                esc_html__('Congrats, the smart bars have been deleted.', 'wookit'),
                [
                    'id' => implode(',', $aListOfSuccess)
                ]
            );
        }

        if (count($aListOfErrors) == count($aPostIDs)) {
            return MessageFactory::factory('rest')
                ->error(
                    sprintf(
                        esc_html__('We could not delete the following smart bar ids: %s',
                            'wookit'),
                        implode(',', $aListOfErrors)
                    ),
                    401
                );
        }

        do_action(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/deleteSmartBars/Deleted',
            [
                'postIDs' => $aListOfSuccess
            ]
        );

        return MessageFactory::factory('rest')
            ->success(
                sprintf(
                    esc_html__('The following ids have been deleted: %s. We could not delete the following ids: %s',
                        'wookit'),
                    implode(',', $aListOfSuccess), implode(',', $aListOfErrors)
                )
            );
    }

    public function getSmartBars(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aData = $oRequest->get_params();
        $aPluck = $aData['pluck'] ?? '';
        unset($aData['pluck']);

        $aResponse = (new SmartBarQueryService())->setRawArgs(
            array_merge(
                $aData,
                []
            )
        )->parseArgs()->query(new PostSkeletonService(), $aPluck);

        if ($aResponse['status'] === 'error') {
            return MessageFactory::factory('rest')->error(
                esc_html__('Sorry, We could not find your popups', 'wookit'),
                $aResponse['code']
            );
        }

        return MessageFactory::factory()->success($aResponse['message'], $aResponse['data']);
    }

    public function createSmartBar(WP_REST_Request $oRequest)
    {
        $aData = [
            'upgrade'   => [
                'isUpgrade' => false,
                'message'   => ''
            ],
            'duplicate' => [
                'isDuplicate' => false,
                'message'     => ''
            ]
        ];

        if (empty($userID = get_current_user_id())) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aPostResponse = (new CreatePostService())->setRawData($oRequest->get_params())
            ->performSaveData();

        if ($aPostResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aPostResponse['message'], $aPostResponse['code']);
        }

        $aResponse = (new AddPostMetaService())->setID($aPostResponse['data']['id'])
            ->addPostMeta($oRequest->get_params());

        if ($aResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
        }

        do_action(
            WOOKIT_PREFIX . 'SmartBar/Controllers/SmartBarAPIController/createSmartBar/Created',
            [
                'postID' => $aPostResponse['data']['id'],
                'data'   => $oRequest->get_params()
            ]
        );

        return MessageFactory::factory('rest')->success($aPostResponse['message'],
            array_merge($aData,
                [
                    'id'   => (string)$aPostResponse['data']['id'],
                    'date' => (string)strtotime(get_the_date('Y-m-d H:i:s', $aPostResponse['data']['id']))
                ]));
    }
}
