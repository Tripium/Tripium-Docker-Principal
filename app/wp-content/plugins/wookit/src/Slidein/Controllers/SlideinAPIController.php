<?php


namespace WooKit\Slidein\Controllers;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Message\MessageDefinition;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\Shared\MultiLanguage\MultiLanguage;
use WooKit\Shared\Post\TraitHandleGetDuplicate;
use WooKit\Shared\Post\TraitHandleGetShowOnPageCampaign;
use WooKit\Shared\Post\TraitPostType;
use WooKit\Slidein\Services\Post\CreatePostService;
use WooKit\Slidein\Services\Post\DeletePostService;
use WooKit\Slidein\Services\Post\PostSkeletonService;
use WooKit\Slidein\Services\Post\SlideinQueryService;
use WooKit\Slidein\Services\Post\UpdatePostService;
use WooKit\Slidein\Services\PostMeta\AddPostMetaService;
use WooKit\Slidein\Services\PostMeta\UpdatePostMetaService;
use WP_REST_Request;

class SlideinAPIController
{
    use TraitMainMiddleware, TraitHandleGetDuplicate, TraitHandleGetShowOnPageCampaign, TraitPostType;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
        add_filter('cmb2_types_esc_textarea', [$this, 'resolveConfigDisplayOnCMB2'], 10, 4);
        add_filter(WOOKIT_PREFIX . 'Filter/General/Controllers/GeneralController/getCampaignsStatus',
            [$this, 'handleSlideinIsEmpty'], 10, 2);
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
        $aPopupResponse = (new SlideinQueryService())->setRawArgs(
            [
                'status' => 'active',
                'limit'  => 50
            ]
        )->parseArgs()->query(new PostSkeletonService(), 'config,date,id,title');
        if (!empty($aPopupResponse['data']['items'])) {
            $aResponse['slidein'] = $aPopupResponse['data']['items'];
        }
        return $aResponse;
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST_BASE, 'slideins',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'createSlideins'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSlideins'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteSlideins'],
                    'permission_callback' => '__return_true'
                ],
            ]
        );

        register_rest_route(WOOKIT_REST_BASE, 'delete-slideins',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'deleteSlideins'],
                    'permission_callback' => '__return_true'
                ],
            ]
        );

        register_rest_route(WOOKIT_REST_BASE, 'me/slideins/publishing',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getMyPublishSlideins'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST_BASE, 'slideins/(?P<id>(\d+))/focus-active',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateForceActive'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST_BASE, 'slideins/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSlidein'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateSlidein'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'PATCH',
                    'callback'            => [$this, 'updateSlidein'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteSlidein'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(WOOKIT_REST_BASE, 'slideins/(?P<id>(\d+))/(?P<pluck>(\w+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getSinglePluck'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function updateForceActive(WP_REST_Request $oRequest)
    {
        $aListOfErrors = [];
        $aListOfSuccess = [];
        $postID = (int)$oRequest->get_param('id');
        try {

            $aConfigs = include plugin_dir_path(__FILE__) . '../Configs/PostType.php';
            $postType = AutoPrefix::namePrefix($aConfigs['post_type']);

            $aResponse = $this->processMiddleware([
                'IsUserLoggedIn',
                'IsCampaignExist',
                'IsCampaignTypeExist',
                //'IsDisableMyShopKitBrandMiddleware'
            ], [
                'postType' => $postType,
                'postID'   => $postID,
                'userID'   => get_current_user_id(),
                'config'   => get_post_meta($postID, AutoPrefix::namePrefix('config'), true)
            ]);

            if ($aResponse['status'] == 'error') {
                throw new Exception($aResponse['message'], $aResponse['code']);
            }

            //lấy duplicate
            $aResponseDuplicate = $this->getDuplicate(
                new SlideinQueryService(),
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
                WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/updateForceActive/Updated',
                [
                    'postID'        => $postID,
                    'deactivateIDs' => array_keys($aListOfSuccess)
                ]
            );

            if (empty($aListOfErrors)) {
                return MessageFactory::factory('rest')->success(
                    esc_html__('Congrats, the slidein has been published.', 'wookit'),
                    [
                        'deactivateIDs' => array_keys($aListOfSuccess)
                    ]
                );
            }

            return MessageFactory::factory('rest')
                ->success(
                    sprintf(
                        'The following slideins have been updated: %s. We could not update the following slideins: %s',
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

    /**
     * @throws Exception
     */
    public function getMyPublishSlideins(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $locale = $oRequest->get_param('locale');
        $showOnPages = $oRequest->get_param('showOnPage');
        $showOnPages = empty($showOnPages) ? 'all' : $showOnPages;

        $aResponse = (new SlideinQueryService())->setRawArgs(
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

    public function handleSlideinIsEmpty($aResponse, $aData)
    {
        $aResponseSlidein = (new SlideinQueryService())->setRawArgs(
            array_merge(
                [
                    'limit' => 1
                ],
                []
            )
        )->parseArgs()->query(new PostSkeletonService(), 'title');

        $aResponse['hasSlidein'] = !empty($aResponseSlidein['data']['items']);

        return $aResponse;
    }

    public function getSlideins(WP_REST_Request $oRequest)
    {
        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aData = $oRequest->get_params();
        $aPluck = $aData['pluck'] ?? '';
        unset($aData['pluck']);

        $aResponse = (new SlideinQueryService())->setRawArgs(
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

    public function getSlidein(WP_REST_Request $oRequest)
    {
        $aParams = $oRequest->get_params();
        $pluck = $aParams['pluck'] ?? '';
        unset($aParams['pluck']);

        $aResponse = (new SlideinQueryService())->setRawArgs($aParams)->parseArgs()
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

        $aResponse = (new SlideinQueryService())->setRawArgs($aParams)->parseArgs()
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

    public function deleteSlidein(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');

        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/deleteSlidein/BeforeDelete',
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
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/deleteSlidein/Deleted',
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

    public function deleteSlideins(WP_REST_Request $oRequest)
    {
        $aPostIDs = explode(',', $oRequest->get_param('ids'));

        if (empty($aPostIDs)) {
            return MessageFactory::factory('rest')->error(
                esc_html__('Please provide 1 slidein at least', 'wookit'),
                400
            );
        }

        if (!is_user_logged_in()) {
            return MessageFactory::factory('rest')
                ->error(MessageDefinition::youMustLogin(), 401);
        }
        $aListOfErrors = [];
        $aListOfSuccess = [];
        $oDeletePostServices = new DeletePostService();

        $aVerifyBeforeDelete = apply_filters(
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/deleteSlideins/BeforeDelete',
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
                esc_html__('Congrats, the slideins have been deleted.', 'wookit'),
                [
                    'id' => implode(',', $aListOfSuccess)
                ]
            );
        }

        if (count($aListOfErrors) == count($aPostIDs)) {
            return MessageFactory::factory('rest')
                ->error(
                    sprintf(
                        esc_html__('We could not delete the following slidein ids: %s',
                            'wookit'),
                        implode(",", $aListOfErrors)
                    ),
                    401
                );
        }

        do_action(
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/deleteSlideins/Deleted',
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
    public function createSlideins(WP_REST_Request $oRequest)
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
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/createSlideins/Created',
            [
                'postID' => $aPostResponse['data']['id'],
                'data'   => $oRequest->get_params()
            ]
        );

        return MessageFactory::factory('rest')->success($aPostResponse['message'],
            array_merge($aData,
                [
                    'id'   => (string)$aPostResponse['data']['id'],
                    'date' => (string)strtotime(get_the_date('Y-m-d H:i:s', (int)$aPostResponse['data']['id']))
                ]
            ));
    }

    public function updateSlidein(WP_REST_Request $oRequest)
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
                new SlideinQueryService(),
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
            WOOKIT_PREFIX . 'Slidein/Controllers/SlideinAPIController/updateSlidein/Updated',
            [
                'postID' => $postID,
                'data'   => $oRequest->get_params()
            ]
        );

        $aResponse = array_merge(
            $aData,
            [
                'id'   => (string)$aPostResponse['data']['id'],
                'date' => (string)strtotime(get_the_modified_date('Y-m-d H:i:s',
                    (int)$aPostResponse['data']['id']))
            ]
        );


//        if (get_post_status($postID) == 'publish') {
//            $aResponse['slidein'] = $this->alertPopupPublish($postID);
//        }

        return MessageFactory::factory('rest')
            ->success(
                $aPostResponse['message'],
                $aResponse
            );
    }
}
