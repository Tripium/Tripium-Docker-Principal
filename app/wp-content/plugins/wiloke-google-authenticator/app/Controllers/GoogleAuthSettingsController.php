<?php

namespace WilokeGoogleAuthenticator\Controllers;

use WilokeGoogleAuthenticator\Helpers\Cookie;
use WilokeGoogleAuthenticator\Helpers\GetOption;
use WilokeGoogleAuthenticator\Helpers\User;
use WilokeGoogleAuthenticator\Helpers\GoogleAuthenticator;

/**
 * Class GoogleAuthenticationController
 * @package WilokeGoogleAuthenticator\Controllers
 */
class GoogleAuthSettingsController
{
    /**
     * @var string
     */
    public $prefix = 'wiloke_';
    
    /**
     * GoogleAuthenticationController constructor.
     */
    public function __construct()
    {
        add_action('cmb2_admin_init', [$this, 'registerGoogleAuthenticatorMenu']);
        add_action('edit_user_profile_update', [$this, 'saveProfile']);
        add_action('personal_options_update', [$this, 'saveProfile']);
        add_action('admin_init', [$this, 'refreshSecretCode']);
        add_action('wp_ajax_verify_otp_before_enable', [$this, 'verifyOTPCodeBeforeEnabling']);
    }
    
    /**
     * @return bool|mixed|object|null
     * @throws \Exception
     */
    private function getMsg()
    {
        $msg = Cookie::getCookie('wga-admin-msg');
        if (empty($msg)) {
            $userID = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : get_current_user_id();
            if (User::isEnableGoogleAuth($userID) && User::isLockedQrCode($userID)) {
                return (object)[
                  'status' => 'success',
                  'msg'    => esc_html__('Wiloke Google Authenticator is enabling',
                    'wiloke-google-authenticator')
                ];
            }
            
            return false;
        }
        
        $oMsg = json_decode($msg);
        if (json_last_error()) {
            $oMsg = json_decode(stripslashes($msg));
        }
        
        return $oMsg;
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    public function refreshSecretCode()
    {
        if (isset($_GET['wga_action']) && $_GET['wga_action'] === 'refresh_secret_code') {
            if (!current_user_can('administrator')) {
                return false;
            }
            
            $userID = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : get_current_user_id();
            $status = User::refreshSecretCode($userID);
            
            if ($status) {
                Cookie::setCookie('wga-admin-msg', json_encode([
                  'status' => 'success',
                  'msg'    => esc_html__('The Secret Code has been renewed', 'wiloke-google-authenticator')
                ]));
                
                wp_safe_redirect(add_query_arg([
                  'user_id' => $userID
                ], admin_url('user-edit.php')));
                exit();
            }
        }
    }
    
    public function verifyOTPCodeBeforeEnabling()
    {
        if (!current_user_can('administrator')) {
            wp_send_json_error(['msg' => 'Forbidden']);
        }
        
        if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
            wp_send_json_error(['msg' => 'The user id is required']);
        }
        
        if (!isset($_POST['otp_code']) || empty($_POST['otp_code'])) {
            wp_send_json_error(['msg' => 'The OTP code id is required']);
        }
        
        try {
            if (GoogleAuthenticator::verifyTwoFactorCode($_POST['otp_code'], $_POST['user_id'])) {
                User::enableGoogleAuth($_POST['user_id']);
                User::setLockedQrCode($_POST['user_id']);
                
                wp_send_json_success(['msg' => 'Congrats! The 2 step factor verification has been enabled']);
            } else {
                wp_send_json_error(['msg' => 'Verification failed']);
            }
        } catch (\Exception $exception) {
            wp_send_json_error(['msg' => $exception->getMessage()]);
        }
    }
    
    public function saveProfile($userId)
    {
        if (!isset($_POST['wiloke_ga_secret_code'])) {
            return false;
        }
        
        if (!current_user_can('administrator')) {
            return false;
        }
        
        $aFields = include WILOKE_GOOGLE_AUTHENTICATOR_PATH.'config/app.php';
        
        $aData = [];
        foreach ($aFields as $aField) {
            if (isset($_POST[$aField['id']])) {
                $aData[str_replace('wiloke_ga_', '', $aField['id'])] = sanitize_text_field($_POST[$aField['id']]);
            }
        }
        
        if ($aData['mode'] === 'disable') {
            try {
                if (User::isEnableGoogleAuth($userId)) {
                    User::enableGoogleAuth($userId);
                }
            } catch (\Exception $e) {
                Cookie::setCookie('wga-admin-msg', json_encode([
                  'status' => 'error',
                  'msg'    => $e->getMessage()
                ]));
            }
            
            return true;
        }
        
        //        try {
        //            User::updateData($userId, $aData);
        //        } catch (\Exception $e) {
        //        }
        //
        //        if (!isset($aData['opt_verification']) || empty($aData['opt_verification'])) {
        //            try {
        //                if (!User::isLockedQrCode($userId)) {
        //                    $aData['mode'] = 'disable';
        //                }
        //            } catch (\Exception $e) {
        //                $aData['mode'] = 'disable';
        //            }
        //        } else {
        //            try {
        //                if (!GoogleAuthenticator::verifyTwoFactorCode($aData['opt_verification'], $userId)) {
        //                    $aData['mode'] = 'disable';
        //                    Cookie::setCookie('wga-admin-msg', json_encode([
        //                      'status' => 'error',
        //                      'msg'    => esc_html__('Verified failed. Please try it again', 'wiloke-google-authenticator')
        //                    ]));
        //                } else {
        //                    $aData['lockedQrCode'] = 'yes';
        //                    Cookie::destroyCookie('wga-admin-msg');
        //                }
        //            } catch (\Exception $e) {
        //                $aData['mode'] = 'disable';
        //            }
        //        }
        //
        //        unset($aData['opt_verification']);
        //        try {
        //            User::updateData($userId, $aData);
        //        } catch (\Exception $e) {
        //        }
    }
    
    /**
     * @throws \Exception
     */
    public function registerGoogleAuthenticatorMenu()
    {
        if (!GetOption::isEnable('wga_toggle') || !is_user_logged_in()) {
            return false;
        }
        
        $cmb_user = new_cmb2_box(
          [
            'id'               => $this->prefix.'google-authentication',
            'title'            => '',
            'object_types'     => ['user'],
            'show_names'       => true,
            'save_fields'      => false,
            'new_user_section' => 'add-existing-user',
          ]
        );
        
        $aFields = include WILOKE_GOOGLE_AUTHENTICATOR_PATH.'config/app.php';
        if (current_user_can('administrator')) {
            $userID = isset($_GET['user_id']) ? $_GET['user_id'] : get_current_user_id();
        } else {
            $userID = get_current_user_id();
        }
        
        foreach ($aFields as $order => $aField) {
            if ($aField['id'] == 'wiloke_ga_opt_verification') {
                if (User::isLockedQrCode($userID) && User::isEnableGoogleAuth($userID)) {
                    unset($aFields[$order]);
                    continue;
                }
            }
            
            switch ($aField['id']) {
                case 'wiloke_ga_secret_code':
                    if (!User::isLockedQrCode($userID)) {
                        $aField['after_field'] = '<p><img src="'.esc_url(User::getField('qrCodeUrl', $userID)).'"></p>';
                        $aField['default']     = User::getField('secret_code', $userID);
                    }
                    
                    $resetBarCodeUrl       = add_query_arg(
                      [
                        'user_id'    => $userID,
                        'wga_action' => 'refresh_secret_code'
                      ],
                      admin_url('user-edit.php')
                    );
                    $aField['after_field'] = $aField['after_field'].'<p>'.
                                             __('Wiloke Google Authenticator is using on this account. If you need to refresh Secret code, please <a id="wga-refresh-secret-code" href="'.
                                                esc_url($resetBarCodeUrl).'">click on me</a>',
                                               'wiloke-google-authentication').'</p><p>'.
                                             esc_html__('Warning: After a new secret code has been generated, the old barcode will be removed, Customer will have to re-scan the new barcode',
                                               'wiloke-google-authentication').'</p>';
                    $aField['default']     = User::getField('secret_code', $userID);
                    break;
                case 'ga_setting':
                    $oMsg = $this->getMsg();
                    if (!empty($oMsg)) {
                        if ($oMsg->status === 'error') {
                            $aField['desc'] = '<p style="color: red">'.str_replace('+', ' ', $oMsg->msg).'</p>';
                        } else {
                            $aField['desc'] = '<p style="color: green">'.str_replace('+', ' ', $oMsg->msg).'</p>';
                        }
                    }
                    break;
                default:
                    $aField['default'] = User::getField(str_replace('wiloke_ga_', '', $aField['id']), $userID);
                    break;
            }
            
            $aFields[$order] = $aField;
            
            $cmb_user->add_field($aField);
        }
    }
}

