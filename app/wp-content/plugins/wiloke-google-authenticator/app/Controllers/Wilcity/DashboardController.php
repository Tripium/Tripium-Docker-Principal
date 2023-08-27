<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\GetOption;
use WilokeGoogleAuthenticator\Helpers\GoogleAuthenticator as VerifyGoogleAuth;
use WilokeGoogleAuthenticator\Helpers\User;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\RetrieveController;

class DashboardController
{
    public function __construct()
    {
        add_filter(
          'wiloke-listing-tools/filter/dashboard/profile/sections',
          [$this, 'addGoogleAuthenticatorToProfile'],
          10,
          2
        );
        add_action(
          'wp_ajax_wilcity_toggle_google_auth',
          [
            $this,
            'handleToggleGoogleAuthenticator'
          ]
        );
        add_action(
          'wp_ajax_wilcity_verify_enable_google_auth',
          [
            $this,
            'verifyEnableGoogleAuth'
          ]
        );
    }
    
    /**
     * In order to enable this feature, We need to verify it first
     */
    public function verifyEnableGoogleAuth()
    {
        $oRetrieve = new RetrieveController(new AjaxRetrieve());
        if (!isset($_POST['google_auth_code']) || empty($_POST['google_auth_code'])) {
            $oRetrieve->error([
              'msg' => esc_html__('Invalid Authentication Code', 'wiloke-listing-tools')
            ]);
        }
        
        $userID = get_current_user_id();
        
        try {
            if (VerifyGoogleAuth::verifyTwoFactorCode($_POST['google_auth_code'], $userID)) {
                User::setLockedQrCode($userID);
                User::enableGoogleAuth($userID);
                
                $oRetrieve->success(
                  [
                    'msg' => esc_html__('The Two-factor authentication has been enabled', 'wiloke-listing-tools')
                  ]
                );
            }
            
            $oRetrieve->error(
              [
                'msg' => esc_html__('Invalid Authentication Code', 'wiloke-listing-tools')
              ]
            );
        } catch (\Exception $exception) {
            return $oRetrieve->error($exception->getMessage());
        }
    }
    
    public function handleToggleGoogleAuthenticator()
    {
        $oRetrieve = new RetrieveController(new AjaxRetrieve());
        
        $invalidPassword = esc_html__('Invalid password', 'wiloke-listing-tools');
        if (!isset($_POST['password']) || empty($_POST['password'])) {
            $oRetrieve->error(['msg' => $invalidPassword]);
        }
        
        $userId = get_current_user_id();
        $oUser  = new \WP_User($userId);
        
        if (!wp_check_password($_POST['password'], $oUser->data->user_pass, $oUser->ID)) {
            $oRetrieve->error(['msg' => $invalidPassword]);
        }
        
        try {
            $mode = User::getField('mode', $oUser->ID);
        } catch (\Exception $e) {
            $oRetrieve->error($e->getMessage());
        }
        
        if ($mode === 'enable') {
            try {
                User::disableGoogleAuth($userId);
            } catch (\Exception $e) {
                $oRetrieve->error($e->getMessage());
            }
            
            $oRetrieve->success([
              'msg'            => esc_html__(
                'Two-factor authentication has been disabled',
                'wiloke-listing-tools'
              ),
              'isLockedQrCode' => true,
              'mode'           => 'disable'
            ]);
        }
        
        try {
            if (User::isLockedQrCode($userId)) {
                User::enableGoogleAuth($userId);
                $oRetrieve->success([
                  'msg'            => esc_html__(
                    'Two-factor authentication has been re-enabled',
                    'wiloke-listing-tools'
                  ),
                  'isLockedQrCode' => true,
                  'mode'           => 'enable'
                ]);
            }
        } catch (\Exception $e) {
            $oRetrieve->error($e->getMessage());
        }
        
        try {
            $oRetrieve->success(
              [
                'qrCodeUrl'      => User::getField('qrCodeUrl'),
                'isLockedQrCode' => false
              ]
            );
        } catch (\Exception $e) {
            $oRetrieve->error($e->getMessage());
        }
    }
    
    public function addGoogleAuthenticatorToProfile($aSections, $userID)
    {
        if (GetOption::isEnableGTA()) {
            try {
                $aSections[] = [
                  'heading'     => esc_html__('Login Security', 'wiloke-google-authentication'),
                  'translation' => 'loginSecurity',
                  'key'         => 'twofactorauth',
                  'fields'      => [
                    [
                      'value' => User::getField('mode', $userID) === 'enable' ? 'yes' : 'no',
                      'type'  => 'wil-switch',
                      'key'   => 'mode',
                      'label' => esc_html__('Use two-factor authentication', 'wiloke-google-authentication')
                    ]
                  ]
                ];
            } catch (\Exception $e) {
            }
        }
        
        return $aSections;
    }
}
