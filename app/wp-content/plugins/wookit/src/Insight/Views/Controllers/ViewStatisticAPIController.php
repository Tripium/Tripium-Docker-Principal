<?php


namespace WooKit\Insight\Views\Controllers;


use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Insight\Interfaces\IInsightController;
use WooKit\Insight\Shared\TraitReportStatistic;
use WooKit\Insight\Shared\TraitVerifyParamStatistic;
use WooKit\Insight\Views\Database\ViewStatisticTbl;
use WooKit\Insight\Views\Models\ViewStatisticModel;
use WooKit\Insight\Shared\TraitUpdateDeleteCreateInsightValidation;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WP_REST_Request;
use WP_REST_Response;

class ViewStatisticAPIController implements IInsightController
{
    use TraitUpdateDeleteCreateInsightValidation;
    use TraitReportStatistic;
    use TraitVerifyParamStatistic;
    use TraitMainMiddleware;

    private string $postType = '';

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
        add_filter(WOOKIT_HOOK_PREFIX . 'Filter/Shared/Plan/TrainCheckImpressionCampaign',
            [$this, 'getImpressionCampaign'], 10, 3);
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'insights/views',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportViews'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(WOOKIT_REST, 'insights/views/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportViewBySpecifyingPostID'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'updateView'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteView'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    /**
     * @param string $postType
     * @param array $aResponse {status:{success || error},message:string,data:{summary:int}}
     * @return array {status:{success || error},message:string,summary:int}
     */
    public function getImpressionCampaign(array $aResponse, string $postType): array
    {
        try {
            $this->postType = $postType;
            $aData = $this->getReport('thisMonth', []);
            if (!empty($aData)) {
                $aResponse = MessageFactory::factory()->success('Passed', [
                    'summary' => $aData['summary']
                ]);
            }
            return $aResponse;
        } catch (Exception $exception) {
            return MessageFactory::factory()->error($exception->getMessage(), [
                'summary' => 0
            ]);
        }
    }

    public function deleteView(WP_REST_Request $oRequest)
    {
        $postType = $oRequest->get_param('postType');
        try {
            $this->verifyParamStatistic($postType);
            $aValidation = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), true,
                AutoPrefix::namePrefix($postType));

            if (($aValidation['status'] == 'success')) {
                $id = ViewStatisticModel::delete($aValidation['data']['postID'], $aValidation['data']['shopID']);
                if (!empty($id)) {
                    return MessageFactory::factory('rest')
                        ->success(esc_html__('The view has deleted in database successfully',
                            'wookit'));
                } else {
                    return MessageFactory::factory('rest')
                        ->error(esc_html__('We could not deleted the view in database',
                            'wookit'), 401);
                }
            } else {
                return MessageFactory::factory('rest')->error($aValidation['message'], $aValidation['code']);
            }
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')
                ->error($exception->getMessage(), $exception->getCode());
        }
    }

    public function reportViews(WP_REST_Request $oRequest)
    {
        $aAdditional = [
            'from' => $oRequest->get_param('from'),
            'to'   => $oRequest->get_param('to')
        ];
        $postType = $oRequest->get_param('postType');
        $filter = $oRequest->get_param('filter');
        $filter = empty($filter) ? 'today' : $filter;

        try {
            $this->verifyParamStatistic($postType, $filter, $aAdditional);
            $this->postType = AutoPrefix::namePrefix($postType);
            $aData = $this->getReport($filter, $aAdditional);

            return MessageFactory::factory('rest')->success(
                'success',
                [
                    'type'     => 'view',
                    'summary'  => $aData['summary'],
                    'timeline' => $aData['timeline']
                ]
            );
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
        }
    }

    public function reportViewBySpecifyingPostID(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');
        $postType = get_post_type($postID);
        $aValidation = $this->processMiddleware([
            'IsCampaignExist',
            'IsCampaignTypeExist'
        ], [
            'postID'   => $postID,
            'postType' => $postType,
        ]);
        if ($aValidation['status'] === 'success') {
            $aAdditional = [
                'from' => $oRequest->get_param('from'),
                'to'   => $oRequest->get_param('to')
            ];
            $filter = $oRequest->get_param('filter');
            $postType = $oRequest->get_param('postType');
            $filter = empty($filter) ? 'today' : $filter;

            try {
                $this->verifyParamStatistic($postType, $filter, $aAdditional);
                $this->postType = AutoPrefix::namePrefix($postType);
                $aData = $this->getReport($filter, $aAdditional, $postID);

                return MessageFactory::factory('rest')->success(
                    'success',
                    [
                        'type'     => 'view',
                        'summary'  => $aData['summary'],
                        'timeline' => $aData['timeline']
                    ]
                );
            } catch (Exception $exception) {
                return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
            }
        } else {
            return MessageFactory::factory('rest')->error(
                $aValidation['message'],
                $aValidation['code']
            );
        }

    }

    /**
     * id la popup id
     *
     * @param WP_REST_Request $oRequest
     *
     * @return WP_REST_Response
     * @throws Exception
     */
    public function updateView(WP_REST_Request $oRequest): WP_REST_Response
    {
        $postType = $oRequest->get_param('postType');
        $postID = $oRequest->get_param('id');
        try {
            $this->verifyParamStatistic($postType);
            $aValidationResponse = $this->processMiddleware([
                'IsCampaignExist',
                'IsCampaignTypeExist'
            ], [
                'postID'   => $postID,
                'postType' => AutoPrefix::namePrefix($postType),
            ]);

            if (($aValidationResponse['status'] == 'success')) {
                $aResult = $this->updateViewedToday(
                    $postID
                );
                if ($aResult['status'] == 'success') {
                    $this->postType = AutoPrefix::namePrefix($postType);
                    $aData = $this->getReport(
                        'today',
                        [],
                        $postID
                    );

                    return MessageFactory::factory('rest')
                        ->success(
                            $aResult['message'],
                            [
                                'id'       => $aResult['data']['id'],
                                'type'     => 'view',
                                'summary'  => $aData['summary'],
                                'timeline' => $aData['timeline']
                            ]
                        );
                } else {
                    return MessageFactory::factory('rest')
                        ->error($aResult['message'], $aResult['code']);
                }
            } else {
                return MessageFactory::factory('rest')
                    ->error($aValidationResponse['message'], $aValidationResponse['code']);
            }
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')
                ->error($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Lưu lại số lượt view trong ngày hôm nay. Nếu đã có view rồi thì tự động tăng thêm 1
     *
     * @param $postID
     *
     * @return array
     */
    public function updateViewedToday($postID): array
    {
        if (ViewStatisticModel::isViewedToday($postID)) {
            $viewID = ViewStatisticModel::getIDWithPostID($postID);
            $total = (int)ViewStatisticModel::getField($viewID, 'total') + 1;
            $status = ViewStatisticModel::update([
                'total' => $total,
                'ID'    => $viewID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()
                    ->success(
                        esc_html__('The view has updated in database successfully', 'wookit'),
                        [
                            'id' => (string)$viewID
                        ]
                    );
            } else {
                return MessageFactory::factory()->error(
                    esc_html__('We could not updated the view', 'wookit'),
                    401
                );
            }
        } else {
            $status = ViewStatisticModel::insert([
                'postID' => $postID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()->success(
                    esc_html__('The view has updated in database successfully', 'wookit'),
                    [
                        'id' => (string)$status
                    ]
                );
            } else {
                return MessageFactory::factory()->error(esc_html__('We could not updated the view', 'wookit'),
                    401);
            }
        }
    }

    public function getTable(): string
    {
        return ViewStatisticTbl::getTblName();
    }

    public function getPostType(): string
    {
        return $this->postType;
    }

    public function generateResponseClass(string $queryFilter): string
    {
        $ucFirstFilter = ucfirst($queryFilter);

        return "WooKit\Insight\Shared\\$ucFirstFilter\\$ucFirstFilter" . "Response";
    }

    public function generateQueryClass(string $queryFilter): string
    {
        $ucFirstFilter = ucfirst($queryFilter);

        return "WooKit\Insight\Shared\\$ucFirstFilter\\$ucFirstFilter" . "Query";
    }

    public function getSummary(): string
    {
        return "SUM(total) as summary";
    }
}
