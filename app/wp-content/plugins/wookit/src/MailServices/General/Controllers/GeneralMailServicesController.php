<?php

namespace WooKit\MailServices\General\Controllers;

use Exception;
use League\Csv\Writer;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Insight\Shared\TraitCheckCustomDateInMonth;
use WooKit\Insight\Shared\TraitUpdateDeleteCreateInsightValidation;
use WooKit\Insight\Shared\TraitVerifyParamStatistic;
use WooKit\Insight\Subscribers\Models\SubscriberStatisticModel;
use WooKit\MailServices\Interfaces\IMailService;
use WooKit\MailServices\Shared\TraitGenerateRestEndpoint;
use WooKit\MailServices\Shared\TraitMailServicesValidation;
use WooKit\Shared\AutoPrefix;
use WooKit\Shared\Middleware\TraitMainMiddleware;
use WooKit\Shared\MultiLanguage\MultiLanguage;
use WooKit\Shared\Post\TraitIsPostAuthor;
use WP_REST_Request;
use WP_REST_Response;

class GeneralMailServicesController
{
	use TraitCheckCustomDateInMonth;
	use TraitIsPostAuthor;
	use TraitMailServicesValidation;
	use TraitUpdateDeleteCreateInsightValidation;
	use TraitGenerateRestEndpoint;
	use TraitVerifyParamStatistic;
	use TraitMainMiddleware;

	protected string $key         = '';
	private array    $defaultInfo
	                              = [
			'name' => '',
			'gdpr' => 1,
		];
	private array    $aFormatDate = ['Y-m-d', 'Y-M-d', 'd-M-Y'];

	public function __construct()
	{
		add_action('rest_api_init', [$this, 'registerRouters']);
	}

	public function registerRouters()
	{
		register_rest_route(
			WOOKIT_REST,
			'subscribers',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getSubscribers'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteSubscribers'],
					'permission_callback' => '__return_true',
				],
			]
		);

        register_rest_route(
            WOOKIT_REST,
            'delete-subscribers',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'deleteSubscribers'],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

		register_rest_route(
			WOOKIT_REST,
			'subscribers/(?P<id>(\d+))',
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'subscribeUserEmail'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getSubscriber'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [$this, 'deleteSubscriber'],
					'permission_callback' => '__return_true',
				],
			]
		);

        register_rest_route(
            WOOKIT_REST,
            'delete-subscribers/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'deleteSubscriber'],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

		register_rest_route(
			WOOKIT_REST,
			'subscribers/export',
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'exportSubscribers'],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			WOOKIT_REST,
			$this->getMailServiceEndpoint(),
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'saveApiKey'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getAllServicesData'],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			WOOKIT_REST,
			$this->getChangeServiceStatusEndPoint(),
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'changeServiceStatus'],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			WOOKIT_REST,
			$this->getListIdEndPoint(),
			[
				[
					'methods'             => 'GET',
					'callback'            => [$this, 'getAllLists'],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'saveListID'],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			WOOKIT_REST,
			$this->getSaveEmailEndPoint(),
			[
				[
					'methods'             => 'POST',
					'callback'            => [$this, 'subscribeEmailManually'],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	public function exportSubscribers(WP_REST_Request $oRequest): WP_REST_Response
	{
		$aDataRecords = [];
		$postID = $oRequest->get_param('postID') ?? '';
		$aResult = $this->checkLimitAndPage($oRequest->get_param('limit'), $oRequest->get_param('page'));
		$format = $oRequest->get_param('format');
		$filter = $oRequest->get_param('filter');
		$order = in_array($upperOrder = strtoupper($oRequest->get_param('order')), ['DESC', 'ASC']) ?
			$upperOrder :
			"DESC";
		$aAdditional = [
			'from' => $oRequest->get_param('from'),
			'to'   => $oRequest->get_param('to'),
		];
		$aWhere = [];
		$formatDate = ((!empty($format)) && (in_array($format, $this->aFormatDate))) ? $format : 'm-d-Y';
		$locale = $oRequest->get_param('locale') ?? '';
		try {
			if (!empty($filter)) {
				switch ($filter) {
					case 'custom':
						if (empty($aAdditional['from'])) {
							throw new Exception(esc_html__('The From is missing, please provide From Date value.',
								'myshopkit'));
						}
						if (empty($aAdditional['to'])) {
							throw new Exception(esc_html__('The To is missing, please provide To Date value.',
								'myshopkit'));
						}
						global $wpdb;
						$aWhere[] = "(DATE(createdDate) >= '" . $wpdb->_real_escape($aAdditional['from']) .
							"' AND DATE(createdDate) <= '" .
							$wpdb->_real_escape($aAdditional['to']) . "')";
						break;
				}
			}
			$aRecords = SubscriberStatisticModel::getEmailAndCreatedDateAndPagination(
				$aResult['limit'],
				$aResult['page'],
				$postID,
				$aWhere,
				$order
			);

			$aHeader = ['ID', 'Email', 'Name', 'Campaign', 'Created Date',];
			$csv = Writer::createFromString();

			//insert the header
			$csv->insertOne($aHeader);
			if (!empty($aRecords)) {
				$i = 1;
				foreach ($aRecords as $order => $aRecord) {
					$index = $this->getCampaignWithPostID($aRecord['postID']) ?? 'email';
					$aDataRecords[$order]['ID'] = $i;
					$aDataRecords[$order]['email'] = $aRecord['email'];
					$aDataRecords[$order]['name'] = (json_decode($aRecord['info'], true))['name'];
					$aDataRecords[$order]['campaign'] = MultiLanguage::setLanguage($locale)->getMessage($index);
					$aDataRecords[$order]['date'] = (string)date($formatDate, strtotime($aRecord['date']));
					$i++;
				}
			} else {
				throw new Exception(esc_html__('Oops! We found no subscriber currently.', 'myshopkit'));
			}
			header('Content-Type: application/vnd.ms-excel; charset=utf-8');
			header("Content-Disposition: attachment; name=Subscriber Report; filename=Subscriber Report.xls");
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Transfer-Encoding: binary");
			$csv->insertAll($aDataRecords);
			echo $csv->toString();
			die;
		}
		catch (Exception $oException) {
			die(!WP_DEBUG ? esc_html__('Something went error! Please contact App author to report the issue.',
				'myshopkit') : $oException->getMessage());
		}
	}

	public function checkLimitAndPage($limit, $page): array
	{
		return [
			'limit' => (int)$limit ?: 1000,
			'page'  => (int)$page ?: 1,
		];
	}

	public function getCampaignWithPostID($postID): string
	{
		$aDataConfig = get_post_meta(
			$postID,
			AutoPrefix::namePrefix('config'),
			true
		);

		return $aDataConfig['goal'] ?? '';
	}

	/**
	 * @throws Exception
	 */
	public function getSubscribers(WP_REST_Request $oRequest)
	{
		$aData = [];
		$aResult = $this->checkLimitAndPage($oRequest->get_param('limit'), $oRequest->get_param('page'));
		$aResponse = SubscriberStatisticModel::getEmailAndCreatedDateAndPagination(
			$aResult['limit'],
			$aResult['page']);
		$total = SubscriberStatisticModel::countAll();
		if ($aResponse) {
			foreach ($aResponse as $aItem) {
				$aData[] = [
					'ID'          => $aItem['postID'],
					'email'       => $aItem['email'],
					'createdDate' => (string)strtotime(date('Y-m-d', strtotime($aItem['date']))),
					'campaign'    => $this->getCampaignWithPostID($aItem['postID']),
				];
			}

			return MessageFactory::factory('rest')->success(
				sprintf(esc_html__('We found %s items', 'myshopkit'), count($aData)),
				[
					'items'    => $aData,
					'maxPages' => ceil($total / $aResult['limit']),
				]
			);
		}

		return MessageFactory::factory()->success(
			esc_html__('We found no items', 'myshopkit'),
			['items' => $aData]
		);
	}

	/**
	 * @throws Exception
	 */
	public function getSubscriber(WP_REST_Request $oRequest)
	{
		$aData = [];
		$postID = $oRequest->get_param('id');
		$locale = $oRequest->get_param('locale') ?: 'en';
		try {
			if (!get_current_user_id()) {
				throw new Exception(esc_html__('Sorry, You do not have permission this perform this action',
					'wookit'), 401);
			}
			$aResponse = $this->processMiddleware([
				'IsCampaignExist',
				'IsCampaignTypeExist',
			], [
				'postID'   => $postID,
				'postType' => get_post_type($postID),
				'locale'   => $locale,
			]);
			if ($aResponse['status'] == 'error') {
				throw new Exception($aResponse['message'], $aResponse['code']);
			}
			$total = SubscriberStatisticModel::countAllWithPostID(
				$postID
			);
			$aResult = $this->checkLimitAndPage($oRequest->get_param('limit'), $oRequest->get_param('page'));
			$aResponse = SubscriberStatisticModel::getEmailAndCreatedDateAndPagination(
				$aResult['limit'],
				$aResult['page'],
				$postID);
			$campaign = $this->getCampaignWithPostID($postID);
			if ($aResponse) {
				foreach ($aResponse as $aItem) {
					$aData[] = [
						'ID'          => $aItem['postID'],
						'email'       => $aItem['email'],
						'createdDate' => (string)strtotime(date('Y-m-d', strtotime($aItem['date']))),
						'campaign'    => $campaign,
					];
				}

				return MessageFactory::factory('rest')->success(
					sprintf(esc_html__('We found %s items', 'myshopkit'), count($aData)),
					[
						'items'    => $aData,
						'maxPages' => ceil($total / $aResult['limit']),
					]
				);
			}

			return MessageFactory::factory()->success(
				esc_html__('We found no items', 'myshopkit'), [
					'items' => $aData,
				]
			);
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')
				->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function deleteSubscriber(WP_REST_Request $oRequest)
	{
		if (!is_user_logged_in()) {
			return MessageFactory::factory('rest')->error(
				esc_html__('Forbidden', 'wookit'),
				403
			);
		}
		if (!$email = $oRequest->get_param('email')) {
			return MessageFactory::factory('rest')->error(
				esc_html__('The email param must not be null and required.', 'myshopkit'),
				400
			);
		}
		$postID = $oRequest->get_param('id');
		$aResponse = $this->handleDeleteEmail($postID, $email);
		if ($aResponse['status'] == 'success') {
			return MessageFactory::factory('rest')->success($aResponse['message'], $aResponse['data']);
		} else {
			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}
	}

	public function handleDeleteEmail($postID, $email)
	{
		try {
			if (!current_user_can('administrator')) {
				throw new Exception(esc_html__('Sorry, You do not have permission this perform this action',
					'wookit'), 401);
			}
			$aResponse = $this->processMiddleware([
				'IsCampaignExist',
				'IsCampaignTypeExist',
			], [
				'postID'   => $postID,
				'postType' => get_post_type($postID),
				'locale'   => 'en',
			]);
			if ($aResponse['status'] == 'error') {
				throw new Exception($aResponse['message'], $aResponse['code']);
			}
			if ($subscriberID = SubscriberStatisticModel::getIDWithPostID($postID, $email)) {
				if (SubscriberStatisticModel::delete($subscriberID)) {
					return MessageFactory::factory()
						->success('Congrats, the mail has been unsubscribed', ['id' => (string)$subscriberID]);
				}
				throw new Exception('We could not has been unsubscribed your email', 401);
			}
			throw new Exception('Sorry, we could not find the email address', 400);
		}
		catch (Exception $exception) {
			return MessageFactory::factory()->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function deleteSubscribers(WP_REST_Request $oRequest)
	{
		if (!is_user_logged_in()) {
			return MessageFactory::factory('rest')->error(
				esc_html__('Forbidden', 'wookit'),
				403
			);
		}
		$aEmail = array_map(function ($email) {
			return trim($email);
		}, explode(',', $oRequest->get_param('emails')));
		$aID = array_map(function ($id) {
			return trim($id);
		}, explode(',', $oRequest->get_param('ids')));
		for ($i = 0; $i < count($aID); $i++) {
			$aResponse = $this->handleDeleteEmail($aID[$i], $aEmail[$i]);
			if ($aResponse['status'] == 'success') {
				$aListOfSuccess[] = $aID[$i];
			} else {
				$aListOfErrors[] = $aID[$i];
			}
		}
		if (empty($aListOfErrors)) {
			return MessageFactory::factory('rest')->success('Congrats, the mail has been unsubscribed.',
				[
					'id' => implode(',', $aListOfSuccess),
				]
			);
		}

		if (count($aListOfErrors) == count($aID)) {
			return MessageFactory::factory('rest')
				->error(sprintf('We could not delete the following subscriber ids: %s', implode(",", $aListOfErrors)),
					401);
		}

		return MessageFactory::factory('rest')
			->success(
				sprintf(
					'The following ids have been deleted: %s. We could not delete the following ids: %s',
					implode(',', $aListOfSuccess), implode(',', $aListOfErrors)
				)
			);
	}

	public function subscribeUserEmail(WP_REST_Request $oRequest): WP_REST_Response
	{
		$locale = $oRequest->get_param('locale') ?: 'en';
		$postType = $oRequest->get_param('postType');
		$fullName = $oRequest->get_param('name') ?: '';
		$gdpr = $oRequest->get_param('gdpr');
		$postID = $oRequest->get_param('id');

		try {
			if (!is_email($email = $oRequest->get_param('email'))) {
				throw new Exception(MultiLanguage::setLanguage($locale)
					->getMessage('emailErrorValid'),
					400);
			}
			$aValidationResponse = $this->processMiddleware([
				'IsCampaignExist',
				'IsCampaignTypeExist',
			], [
				'postID'   => $postID,
				'postType' => AutoPrefix::namePrefix($postType),
				'locale'   => $locale,
			]);
			if (($aValidationResponse['status'] == 'success')) {
				$aPostMetaConfigs = get_post_meta($postID, AutoPrefix::namePrefix('config'), true);
				if ($aPostMetaConfigs['settings']['gdpr']) {
					if (empty($gdpr)) {
						throw new Exception(MultiLanguage::setLanguage($locale)
							->getMessage('subscriberErrorGDPR'),
							400);
					}
				}

				$aInfo = wp_parse_args([
					'fullName' => $fullName,
					'gdpr'     => (($gdpr === null) || ($gdpr === 1)) ? 1 : 0,
				], $this->defaultInfo);
				$userID = get_post_field('post_author', $postID);
				$aData = [
					'postID' => $postID,
					'userID' => $userID,
					'email'  => $email,
					'info'   => json_encode($aInfo),
				];

				if (!SubscriberStatisticModel::isEmailExist($email, $postID)) {
					$id = SubscriberStatisticModel::insert($aData);
				} else {
					$id = SubscriberStatisticModel::getEmailID($email, $postID);
				}

				if (!empty($id)) {
					$aData['ID'] = $id;
					do_action(
						WOOKIT_HOOK_PREFIX . 'after/subscribed',
						$aData
					);
				} else {
					throw new Exception(esc_html__('We could not insert the subscriber', 'myshopkit'), 401);
				}

				return MessageFactory::factory('rest')
					->success(
						esc_html__('Thank for your subscription', 'myshopkit'));
			} else {
				throw new Exception($aValidationResponse['message'], $aValidationResponse['code']);
			}
		}
		catch (Exception $exception) {
			return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
		}
	}

	public function getAllServicesData(WP_REST_Request $oRequest): WP_REST_Response
	{
		$aResponse = $this->checkIsUserLoggedIn();
		if ($aResponse['status'] == 'success') {
			$aMailServices = $this->getAllMailServices();
			$aData = [];
			foreach ($aMailServices as $mailService) {
				/**
				 * @var  IMailService $oMailServiceInit
				 */
				$class = $mailService['class'];
				$oMailServiceInit = new $class;
				$campaignID = $oRequest->get_param('campaignID');
				$aResponseCheckCampaignID = $this->checkIsCampaignIDValid($campaignID);
				if ($aResponseCheckCampaignID['status'] == 'success') {
					$aServiceData = $oMailServiceInit->getAllServiceData($campaignID)['data'];
					$aListData = $aServiceData['activatedList'];
					$aApiKeys = $aServiceData['aApiKeys'];
					$serviceName = $aServiceData['serviceName'];
					$status = $aServiceData['status'];
					$selectedStatus = $aServiceData['selected'];
					$serviceLabel = $mailService['label'];
					$description = $mailService['description'];
					$aResponse['label'] = $serviceLabel;
					$aResponse['lists'] = null;
					$aResponse['name'] = $serviceName;
					$aResponse['description'] = $description;
					$aResponse['listItemSelected'] = $aListData;
					$aResponse['apiKeys'] = $aApiKeys;
					$aResponse['status'] = $status;
					$aResponse['selected'] = $selectedStatus;
					$aData[] = $aResponse;
				} else {
					return MessageFactory::factory('rest')
						->error($aResponseCheckCampaignID['message'],
							$aResponseCheckCampaignID['code']
						);
				}
			}

			return MessageFactory::factory('rest')->success(
				esc_html__('This is all of your mail service data', 'myshopkit'),
				[
					'mailServices' => $aData,
				]
			);
		}

		return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
	}

	private function getAllMailServices()
	{
		return include(WOOKIT_PATH . 'src/MailServices/Configs/MailServiceConfig.php');
	}

	public function saveApiKey(WP_REST_Request $oRequest): WP_REST_Response
	{
		if (!empty($oRequest->get_param('mailService'))) {
			$params = $oRequest->get_params();
			$aResponse = $this->checkMailService($params['mailService']);
			if ($aResponse['status'] == 'success') {
				$oInit = $aResponse['data']['oInit'];
				$campaignID = $oRequest->get_param('campaignID');
				$aResponseCheckListID = $this->checkIsCampaignIDValid($campaignID);
				if ($aResponseCheckListID['status'] == 'success') {
					$aResponseSaveApiKey = $oInit->saveApiKey($params);
					if ($aResponseSaveApiKey['status'] == 'success') {
						$aServiceConfig = $this->getAllMailServices();
						$aData = $aResponseSaveApiKey['data'];
						$aData['label'] = $aServiceConfig[$params['mailService']]['label'];
						$aData['description'] = $aServiceConfig[$params['mailService']]['description'];

						return MessageFactory::factory('rest')->success($aResponse['message'], $aData ?? '');
					}

					return MessageFactory::factory('rest')
						->error($aResponseSaveApiKey['message'], $aResponseSaveApiKey['code']);
				}

				return MessageFactory::factory('rest')
					->error($aResponseCheckListID['message'], $aResponseCheckListID['code']);
			}

			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}

		return MessageFactory::factory('rest')
			->error(esc_html__('Look like your Mail Service Field has been left empty. Please re-check it! ',
				'myshopkit'), 400);
	}

	private function checkMailService($mailServiceKey): array
	{
		if (array_key_exists($mailServiceKey, $this->getAllMailServices())) {
			$class = $this->getAllMailServices()[$mailServiceKey]['class'];

			return MessageFactory::factory()->success('OK',
				[
					'oInit' => new $class,
				]
			);
		}

		return MessageFactory::factory()->error(esc_html__('Look like we don\'t have that service yet', 'myshopkit'),
			404);
	}

	public function getAllLists(WP_REST_Request $oRequest): WP_REST_Response
	{
		if (!empty($oRequest->get_param('mailService'))) {
			$params = $oRequest->get_params();
			$aResponse = $this->checkMailService($params['mailService']);
			if ($aResponse['status'] == 'success') {
				$oInit = $aResponse['data']['oInit'];

				$aResponse = $oInit->getAllLists();
				if ($aResponse['status'] == 'success') {
					return MessageFactory::factory('rest')
						->success($aResponse['message'], $aResponse['data'] ?? null);
				}

				return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
			}

			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}

		return MessageFactory::factory('rest')
			->error(esc_html__('Look like your Mail Service Field has been left empty. Please re-check it! ',
				'myshopkit'), 400);
	}

	public function saveListID(WP_REST_Request $oRequest): WP_REST_Response
	{
		if (!empty($oRequest->get_param('mailService'))) {
			$params = $oRequest->get_params();
			$aResponse = $this->checkMailService($params['mailService']);
			if ($aResponse['status'] == 'success') {
				$oInit = $aResponse['data']['oInit'];
				$campaignID = $oRequest->get_param('campaignID');
				$aResponseCheckListID = $this->checkIsCampaignIDValid($campaignID);
				if ($aResponseCheckListID['status'] == 'success') {
					$aResponse = $oInit->saveListID($params);
					if ($aResponse['status'] == 'error') {
						return MessageFactory::factory('rest')->response($aResponse);
					}

					return MessageFactory::factory('rest')
						->success($aResponse['message'], $aResponse['data'] ?? '');
				}

				return MessageFactory::factory('rest')
					->error($aResponseCheckListID['message'], $aResponseCheckListID['code']);
			}

			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}

		return MessageFactory::factory('rest')
			->error(esc_html__('Look like your Mail Service Field has been left empty. Please re-check it! ',
				'myshopkit'), 400);
	}

	public function changeServiceStatus(WP_REST_Request $oRequest): WP_REST_Response
	{
		if (!empty($oRequest->get_param('mailService'))) {
			$params = $oRequest->get_params();
			$aResponse = $this->checkMailService($params['mailService']);
			if ($aResponse['status'] == 'success') {
				$oInit = $aResponse['data']['oInit'];
				$campaignID = $oRequest->get_param('campaignID');
				$aResponseCheckListID = $this->checkIsCampaignIDValid($campaignID);
				if ($aResponseCheckListID['status'] == 'success') {
					$aResponse = $oInit->changeServiceStatus($params);

					return MessageFactory::factory('rest')->success($aResponse['message']);
				}

				return MessageFactory::factory('rest')
					->error($aResponseCheckListID['message'], $aResponseCheckListID['code']);
			}

			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}

		return MessageFactory::factory('rest')
			->error(esc_html__('Look like your Mail Service Field has been left empty. Please re-check it! ',
				'myshopkit'), 400);
	}

	public function subscribeEmailManually(WP_REST_Request $oRequest): WP_REST_Response
	{
		if (!empty($oRequest->get_param('mailService'))) {
			$params = $oRequest->get_params();
			$aResponse = $this->checkMailService($params['mailService']);
			if ($aResponse['status'] == 'success') {
				$oInit = $aResponse['data']['oInit'];
				$campaignID = $oRequest->get_param('campaignID');
				$aResponseCheckListID = $this->checkIsCampaignIDValid($campaignID);
				if ($aResponseCheckListID['status'] == 'success') {
					$aResponse = $oInit->subscribeEmailManually($params);

					return MessageFactory::factory('rest')->success($aResponse['message']);
				}

				return MessageFactory::factory('rest')
					->error($aResponseCheckListID['message'], $aResponseCheckListID['code']);
			}

			return MessageFactory::factory('rest')->error($aResponse['message'], $aResponse['code']);
		}

		return MessageFactory::factory('rest')
			->error(esc_html__('Look like your Mail Service Field has been left empty. Please re-check it! ',
				'myshopkit'), 400);
	}
}
