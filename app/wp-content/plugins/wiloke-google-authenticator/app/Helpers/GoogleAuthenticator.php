<?php

namespace WilokeGoogleAuthenticator\Helpers;

/**
 * Class GoogleAuthenticator
 * @package WilokeGoogleAuthenticator\Helpers
 */
class GoogleAuthenticator
{
    /**
     * @param $iUserID
     * @param $sOtp
     *
     * @return bool
     * @throws \Exception
     */
    public static function verifyTwoFactorCode($sOtp, $iUserID)
    {
        $oAuthenticator = new \PHPGangsta_GoogleAuthenticator();
        $secretCode     = User::getField('secret_code', $iUserID);
        $isVerified     = $oAuthenticator->verifyCode($secretCode, $sOtp, 0);
        return empty($isVerified) ? false : $isVerified;
    }
}
