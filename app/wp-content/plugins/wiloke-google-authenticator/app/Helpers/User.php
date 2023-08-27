<?php

namespace WilokeGoogleAuthenticator\Helpers;


use PHPGangsta_GoogleAuthenticator;

/**
 * Class User
 * @package WilokeGooglaAuthenticator\Helpers
 */
class User
{
    /**
     * @var
     */
    private static $userId;
    private static $key = 'wiloke_google_authentication';

    /**
     * @return array
     */
    private static function createSecret()
    {
        $oAuthenticator = new PHPGangsta_GoogleAuthenticator();
        try {
            $sSecretCode = $oAuthenticator->createSecret();

            return [
              'status'      => 'success',
              'url'         => self::getQrCodeUrl($sSecretCode),
              'secret_code' => $sSecretCode
            ];
        } catch (\Exception $exception) {
            return [
              'status' => 'error',
              'msg'    => $exception->getMessage()
            ];
        }
    }

    public static function getQrCodeUrl($secret)
    {
        $oAuthenticator = new PHPGangsta_GoogleAuthenticator();
        $name           = GetOption::getOptionDetail('wga_name', home_url('/'));
        $oUser          = new \WP_User(self::$userId);

        return $oAuthenticator->getQRCodeGoogleUrl($oUser->user_login, $secret, $name);
    }

    public static function getQrCode($secret)
    {
        $oAuthenticator = new PHPGangsta_GoogleAuthenticator();

        return $oAuthenticator->getCode($secret);
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    private static function setDefault($userId = null)
    {
        self::getUserId($userId);
        if (!is_user_logged_in()) {
            return [
              'status' => 'error',
              'msg'    => esc_html__('You must be logged into the site first', 'wiloke-google-authenticator')
            ];
        }

        $aResponse = self::createSecret();

        if ($aResponse['status'] === 'success') {
            $status = update_user_meta(
              self::$userId,
              self::$key,
              [
                'secret_code'  => $aResponse['secret_code'],
                'mode'         => 'disable',
                'lockedQrCode' => 'no'
              ]
            );

            return $status;
        }

        return false;
    }

    /**
     * @param null $userID
     *
     * @return bool
     * @throws \Exception
     */
    public static function setLockedQrCode($userID = null)
    {

        self::getUserId($userID);

        $aInfo = self::getFields($userID);

        if (!is_array($aInfo)) {
            return false;
        }

        $aInfo['lockedQrCode'] = 'yes';

        return update_user_meta(self::$userId, self::$key, $aInfo);
    }

    /**
     * @param null $userId
     *
     * @return bool|int
     * @throws \Exception
     */
    public static function refreshSecretCode($userId = null)
    {
        self::getUserId($userId);
        $aInfo                 = self::getFields(self::$userId);
        $aInfo['secret_code']  = self::createSecret()['secret_code'];
        $aInfo['lockedQrCode'] = 'no';
        $aInfo['mode']         = 'disable';

        return update_user_meta(self::$userId, self::$key, $aInfo);
    }

    /**
     * @param null $userID
     *
     * @return bool|int
     * @throws \Exception
     */
    public static function setUnLockedQrCode($userID = null)
    {
        self::getUserId($userID);

        $aInfo = self::getFields(self::$userId);

        if (!is_array($aInfo)) {
            return false;
        }

        $aInfo['lockedQrCode'] = 'no';

        return update_user_meta(self::$userId, self::$key, $aInfo);
    }

    /**
     * Locked means customer enabled it and test it for first time already
     *
     * @param null $userID
     *
     * @return bool
     * @throws \Exception
     */
    public static function isLockedQrCode($userID = null)
    {
        $status = self::getField('lockedQrCode', $userID);

        return $status === 'yes';
    }

    /**
     * @param null $userID
     *
     * @return bool
     * @throws \Exception
     */
    public static function isEnableGoogleAuth($userID = null)
    {
        $status = self::getField('mode', $userID);

        return $status === 'enable';
    }

    /**
     * @param null $userID
     *
     * @return bool
     * @throws \Exception
     */
    public static function disableGoogleAuth($userID = null)
    {
        self::getUserId($userID);
        $aFields         = self::getFields(self::$userId);
        $aFields['mode'] = 'disable';

        $status = update_user_meta(self::$userId, self::$key, $aFields);

        if ($status) {
            do_action('wiloke-google-authenticator/after/disabled', self::$userId);
        }
    }

    /**
     * @param null $userID
     *
     * @return bool|int
     * @throws \Exception
     */
    public static function enableGoogleAuth($userID = null)
    {
        self::getUserId($userID);
        $aFields         = self::getFields(self::$userId);
        $aFields['mode'] = 'enable';

        $status = update_user_meta(self::$userId, self::$key, $aFields);

        if ($status) {
            do_action('wiloke-google-authenticator/after/enabled', self::$userId);
        }
    }

    /**
     * @param null $userID
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getFields($userID = null)
    {
        self::getUserId($userID);

        return get_user_meta(self::$userId, self::$key, true);
    }

    /**
     * @param string $field
     * @param bool   $isSetDefaultIfEmpty
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public static function getField($field = '', $userId = null, $isSetDefaultIfEmpty = true)
    {
        $aInfo = User::getFields($userId);
        if (empty($aInfo)) {
            if (!$isSetDefaultIfEmpty) {
                return false;
            }

            $status = self::setDefault();

            if (!$status) {
                return false;
            }

            $aInfo = get_user_meta(self::$userId, self::$key, true);
        }

        $aInfo['qrCodeUrl'] = self::getQrCodeUrl($aInfo['secret_code']);
        if (empty($field)) {
            return $aInfo;
        }

        return array_key_exists($field, $aInfo) ? $aInfo[$field] : false;
    }

    /**
     * @param $userId
     * @param $aData
     *
     * @throws \Exception
     */
    public static function updateData($userId, $aData)
    {
        self::getUserId($userId);

        return update_user_meta(self::$userId, self::$key, $aData);
    }

    /**
     * @param null $userID
     * @param      $field
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getUserMeta($field, $userID = null)
    {
        self::getUserId($userID);

        return get_user_meta(self::$userId, $field, true);
    }

    /**
     * @param null $userId
     *
     * @return int|null
     * @throws \Exception
     */
    private static function getUserId($userId = null)
    {
        self::$userId = empty($userId) ? get_current_user_id() : $userId;

        return self::$userId;
    }
}
