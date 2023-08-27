<?php


namespace WooKit\Popup\Controllers;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Popup\Services\Post\CreatePostService;
use WooKit\Popup\Services\Post\DeletePostService;
use WooKit\Popup\Services\Post\PopupQueryService;
use WooKit\Popup\Services\Post\PostSkeletonService;
use WooKit\Popup\Services\Post\UpdatePostService;
use WooKit\Popup\Services\PostMeta\AddPostMetaService;
use WooKit\Popup\Services\PostMeta\UpdatePostMetaService;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Message\MessageDefinition;

use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\Shared\MultiLanguage\MultiLanguage;
use WooKit\Shared\Post\PostHelper;

use WooKit\Shared\Post\TraitHandleGetDuplicate;
use WooKit\Shared\Post\TraitHandleGetShowOnPageCampaign;
use WP_REST_Request;

class PopupAPIController
{
    use TraitMainMiddleware, TraitHandleGetDuplicate, TraitHandleGetShowOnPageCampaign;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
        add_filter('cmb2_types_esc_textarea', [$this, 'resolveConfigDisplayOnCMB2'], 10, 4);
        add_filter(WOOKIT_PREFIX . 'Filter/General/Controllers/GeneralController/getCampaignsStatus',
            [$this, 'handlePopupIsEmpty'], 10, 2);
        add_filter(WOOKIT_PREFIX . 'Filter/PostScript/Controllers/PostScriptController/getCampaignsActive',
            [$this, 'getCampaignsActive']);
    }

    public static function getConfig($aFieldArgs, $oField)
    {
        $config = get_post_field($oField->object_id, AutoPrefix::namePrefix('config'), true);

        return !is_array($config) ? '' : json_encode($config);
    }

    public function getCampaignsActive(array $aResponse): array
    {
        $aPopupResponse = (new PopupQueryService())->setRawArgs(
            [
                'status' => 'active',
                'limit'  => 50
            ]
        )->parseArgs()->query(new PostSkeletonService(), 'config,date,id,title');
        if (!empty($aPopupResponse['data']['items'])) {
            $aResponse['popup'] = $aPopupResponse['data']['items'];
        }
        return $aResponse;
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'popups',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'createPopup'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getPopups'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'delete-popups',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'deletePopups'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'me/popups/publishing',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getMyPublishPopups'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'popups/(?P<id>(\d+))/focus-active',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateForceActive'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'popups/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getPopup'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updatePopup'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deletePopup'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST, 'popups/(?P<id>(\d+))/(?P<pluck>(\w+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSinglePluck'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function handlePopupIsEmpty($aResponse, $aData)
    {
        $aResponsePopup = (new PopupQueryService())->setRawArgs(
            array_merge(
                [
                    'limit' => 1
                ],
                []
            )
        )->parseArgs()->query(new PostSkeletonService(), 'title');

        $aResponse['hasPopup'] = !empty($aResponsePopup['data']['items']);
        return $aResponse;
    }

    public function updateForceActive(WP_REST_Request $oRequest)
    {
        $aListOfErrors = [];
        $aListOfSuccess = [];
        $postID = (int)$oRequest->get_param('id');
        try {
            $aResponse = $this->processMiddleware([
                'IsUserLoggedIn',
                'IsCampaignExist',
                'IsCampaignTypeExist',
                //'IsDisableMyShopKitBrandMiddleware'
            ], [
                'postType' => AutoPrefix::namePrefix('popup'),
                'postID'   => $postID,
                'userID'   => get_current_user_id(),
                'config'   => get_post_meta($postID, AutoPrefix::namePrefix('config'), true)
            ]);

            if ($aResponse['status'] == 'error') {
                throw new Exception($aResponse['message'], $aResponse['code']);
            }

            //lấy duplicate
            $aResponseDuplicate = $this->getDuplicate(
                new PopupQueryService(),
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
                    esc_html__('Oops! Something went error, We could not active the Campaign.', 'wookit'),
                    [
                        'deactivateIDs' => [$postID]
                    ]
                );
            }

            do_action(
                WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/updateForceActive/Updated',
                [
                    'postID'        => $postID,
                    'deactivateIDs' => array_keys($aListOfSuccess)
                ]
            );

            if (empty($aListOfErrors)) {
                return MessageFactory::factory('rest')->success(
                    esc_html__('Congrats, the popup has been published.', 'wookit'),
                    [
                        'deactivateIDs' => array_keys($aListOfSuccess)
                    ]
                );
            }

            return MessageFactory::factory('rest')
                ->success(
                    sprintf(
                        'The following popups have been updated: %s. We could not update the following popups: %s',
                        implode(',', $aListOfSuccess), implode(',', $aListOfErrors)
                    ),
                    [
                        'deactivateIDs' => array_keys($aListOfSuccess)
                    ]
                );
        } catch (Exception $oException) {
            return MessageFactory::factory('rest')->error($oException->getMessage(), $oException->getCode());
        }
    }

    public function resolveConfigDisplayOnCMB2($nothing, $value, $oField, $that)
    {
        return is_array($that->value) ? json_encode($that->value) : $nothing;
    }

    public function getMyPublishPopups(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $locale = $oRequest->get_param('locale');
        $showOnPages = $oRequest->get_param('showOnPage');
        $showOnPages = empty($showOnPages) ? 'all' : $showOnPages;

        $aResponse = (new PopupQueryService())->setRawArgs(
            array_merge(
                [
                    'status'     => 'active',
                    'showOnPage' => $showOnPages
                ],
                [
                    'author' => get_current_user_id()
                ]
            )
        )->parseArgs()->query(new PostSkeletonService(), 'id,title');

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

    public function getPopups(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aData = $oRequest->get_params();
        $aPluck = $aData['pluck'] ?? '';
        unset($aData['pluck']);

        $aResponse = (new PopupQueryService())->setRawArgs(
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

    public function getPopup(WP_REST_Request $oRequest)
    {
        $aParams = $oRequest->get_params();
        $pluck = $aParams['pluck'] ?? '';
        unset($aParams['pluck']);

        $aResponse = (new PopupQueryService())->setRawArgs($aParams)->parseArgs()
            ->query(new PostSkeletonService(), $pluck, true);

        if ($aResponse['status'] == 'success') {
            return MessageFactory::factory('rest')->success($aResponse['message'], $aResponse['data']);
        } else {
            return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
        }
    }

    public function getSinglePluck(WP_REST_Request $oRequest)
    {
        $aParams = $oRequest->get_params();
        $pluck = $aParams['pluck'];
        unset($aParams['pluck']);

        $aResponse = (new PopupQueryService())->setRawArgs($aParams)->parseArgs()
            ->query(new PostSkeletonService(), $pluck, true);

        if ($aResponse['status'] == 'success') {
            if (isset($aResponse['data'][$pluck])) {
                return MessageFactory::factory('rest')->success(
                    $aResponse['message'],
                    $aResponse['data'][$pluck]
                );
            }

            return MessageFactory::factory('rest')->success(
                $aResponse['message']
            );
        } else {
            return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
        }
    }

    public function deletePopup(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');

        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/deletePopup/BeforeDelete',
            [
                'ID' => $postID
            ]
        );

        if ((isset($aVerifyBeforeDelete['status'])) && ($aVerifyBeforeDelete['status'] == 'error')) {
            return MessageFactory::factory('rest')->response($aVerifyBeforeDelete);
        }

        $aPostResponse = (new DeletePostService())->setID($postID)->delete();
        if ($aPostResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aPostResponse['message'], $aPostResponse['code']);
        }

        do_action(
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/deletePopup/Deleted',
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

    public function deletePopups(WP_REST_Request $oRequest)
    {
        if (empty($oRequest->get_param('ids'))) {
            return MessageFactory::factory('rest')->error(
                esc_html__('Please provide 1 popup at least', 'wookit'),
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
        $oDeletePostServices = new DeletePostService();

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/deletePopups/BeforeDelete',
            [
                'IDs' => $aPostIDs
            ]
        );
        if ((isset($aVerifyBeforeDelete['status'])) && ($aVerifyBeforeDelete['status'] == 'error')) {
            return MessageFactory::factory('rest')->response($aVerifyBeforeDelete);
        }

        $aPostIDs = $aVerifyBeforeDelete['IDs'];
        if (isset($aVerifyBeforeDelete['data']['errorCampaignIDs']) &&
            !empty($aVerifyBeforeDelete['data']['errorCampaignIDs'])) {
            $aListOfErrors = $aVerifyBeforeDelete['data']['errorCampaignIDs'];
        }

        foreach ($aPostIDs as $postID) {
            $aDeleteResponse = $oDeletePostServices->setID($postID)->delete();
            if ($aDeleteResponse['status'] === 'error') {
                $aListOfErrors[] = $postID;
            } else {
                $aListOfSuccess[] = $postID;
            }
        }

        if (empty($aListOfErrors)) {
            return MessageFactory::factory('rest')->success(
                esc_html__('Congrats, the popups have been deleted.', 'wookit'),
                [
                    'id' => implode(',', $aListOfSuccess)
                ]
            );
        }

        if (count($aListOfErrors) == count($aPostIDs)) {
            return MessageFactory::factory('rest')
                ->error(
                    sprintf(
                        esc_html__('We could not delete the following popup ids: %s',
                            'wookit'),
                        implode(",", $aListOfErrors)
                    ),
                    401
                );
        }

        do_action(
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/deletePopups/Deleted',
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

    /**
     * @throws Exception
     */
    public function createPopup(WP_REST_Request $oRequest)
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

        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aPostResponse = (new CreatePostService())->setRawData($oRequest->get_params())
            ->performSaveData();

        if ($aPostResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aPostResponse['message'], $aPostResponse['code']);
        }

        $aResponse = (new AddPostMetaService())->setID($aPostResponse['data']['id'])->addPostMeta(
            $oRequest->get_params());

        if ($aResponse['status'] == 'error') {
            return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
        }

        do_action(
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/createPopup/Created',
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
                ]
            ));
    }

    public function updatePopup(WP_REST_Request $oRequest)
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

            $showOnPage = (isset($aConfig['targeting']['showOnPage']) &&
                !empty($aConfig['targeting']['showOnPage']))
                ? $aConfig['targeting']['showOnPage'] : $this->getShowOnPageCampaign($postID);

            $aResponse = $this->getDuplicate(
                new PopupQueryService(),
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
            WOOKIT_PREFIX . 'Popup/Controllers/PopupAPIController/updatePopup/Updated',
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
                    ]));
    }
}
