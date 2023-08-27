<?php

namespace WilokeGoogleAuthenticator\Controllers\Wilcity;

use WilokeGoogleAuthenticator\Helpers\Session;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\HTML;
use WilokeMessage;

class OTPVerificationFormController
{
    public function __construct()
    {
        add_action(
          'wilcity/wilcity-shortcodes/default-sc/wilcity-custom-login/form/wilcity_verify_otp',
          [$this, 'renderForm']
        );
        add_shortcode('wiloke_google_authenticator_opt_verification', [$this, 'renderVerification']);
    }
    
    public function renderVerification()
    {
        ob_start();
        $this->renderForm();
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    public function renderForm($handleAction = '')
    {
        if ($msg = Session::getSessionOTPError(true)) {
            WilokeMessage::message([
              'status'       => 'danger',
              'hasRemoveBtn' => false,
              'hasMsgIcon'   => false,
              'msgIcon'      => 'la la-envelope-o',
              'msg'          => $msg
            ]);
        }
        
        $handleAction = empty($handleAction) ? add_query_arg(['action' => 'wilcity_verify_otp'],
          GetSettings::getCustomLoginPage()) : $handleAction;
        ?>
        <p class="wilcity-otp-desc"><strong><?php esc_html_e('Enter in 6 digit code from your Google Authenticator or 3rd party app below',
              'wiloke-google-authenticator') ?></strong></p>
        <form name="verify_otp_form" id="verify_otp_form"
              action="<?php echo esc_url($handleAction, 'login_post'); ?>"
              method="POST"
              novalidate="novalidate">
            <?php
            $aFields = include WILOKE_GOOGLE_AUTHENTICATOR_CONFIG_PATH.'wilcity-otp-form.php';
            foreach ($aFields as $aField) {
                HTML::renderDynamicField($aField);
            }
            
            HTML::renderHiddenField([
              'name'  => 'action',
              'value' => 'wilcity_verify_otp'
            ]);
            
            HTML::renderHiddenField([
              'name'  => 'form_type',
              'value' => 'custom_login'
            ]);
            ?>
            <button type="submit"
                    class="wil-btn mb-20 wil-btn--gradient wil-btn--md wil-btn--round wil-btn--block">
                <?php esc_html_e('Validate', 'wiloke-google-authenticator'); ?></button>
        </form>
        <?php
    }
}
