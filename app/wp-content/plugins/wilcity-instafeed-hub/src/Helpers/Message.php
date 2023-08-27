<?php

namespace WilokeInstagramFeedhub\Helpers;

/**
 * Class Message
 * @package WilokeInstaFeedHub\Helpers
 */
class Message
{
	/**
	 * @param string $msg
	 * @param int $statusCode
	 * @return array|\WP_REST_Response
	 */
	public static function error($msg = '', $statusCode = 404)
	{
		if (wp_doing_ajax()) {
			wp_send_json_error([
				'error' => $msg
			]);
		}

		$oResponse = new \WP_REST_Response([
			'status' => 'error',
			'msg'    => $msg,
		], $statusCode);

		return $oResponse;
	}

	/**
	 * @param $aStatus
	 *
	 * @return bool
	 */
	public static function isError($aStatus)
	{
		if ($aStatus instanceof \WP_REST_Response) {
			if ($aStatus->is_error()) {
				return true;
			}
		} elseif (is_array($aStatus)) {
			if (isset($aStatus['status'])) {
				if ($aStatus['status'] == 'error') {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $aData
	 * @return array
	 */
	public static function success($aData)
	{
		$aDefault = [
			'status' => 'success'
		];

		$aData = wp_parse_args($aData, $aDefault);

		if (wp_doing_ajax()) {
			wp_send_json_success($aData);
		}

		return $aData;
	}
}
