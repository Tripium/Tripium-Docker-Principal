<?php

namespace WooKit\Insight\Subscribers\Controllers;

use Exception;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Insight\Interfaces\IInsightController;
use WooKit\Insight\Shared\TraitCheckCustomDateInMonth;
use WooKit\Insight\Shared\TraitReportStatistic;
use WooKit\Insight\Shared\TraitUpdateDeleteCreateInsightValidation;
use WooKit\Insight\Shared\TraitVerifyParamStatistic;
use WooKit\Insight\Subscribers\Database\SubscriberStatisticTbl;
use WooKit\Shared\AutoPrefix;
use WP_REST_Request;

class SubscriberAPIController implements IInsightController
{
    use TraitUpdateDeleteCreateInsightValidation;
    use TraitCheckCustomDateInMonth;
    use TraitReportStatistic;
    use TraitVerifyParamStatistic;

    private string $postType = '';

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
    }

    public function registerRouters()
    {
        register_rest_route(WOOKIT_REST, 'insights/subscribers',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportSubscribers'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function reportSubscribers(WP_REST_Request $oRequest)
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
                    'type'     => 'subscriber',
                    'summary'  => $aData['summary'],
                    'timeline' => $aData['timeline']
                ]
            );
        } catch (Exception $exception) {
            return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
        }
    }

    public function getTable(): string
    {
        return SubscriberStatisticTbl::getTblName();
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
        return "Count(email) as summary";
    }
}
