<?php


namespace WilokeOTPLogin\Controllers\Wilcity;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\HTML;
use WilokeListingTools\Framework\Store\Session;
use WilokeMessage;

class CustomLoginPageController
{
	private $action   = 'otp_login';
	private $formType = 'custom_login';
	private $currentStep;
	private $nextStep;
	private $aVerificationResponse;

	public function __construct()
	{
		add_action('wp_loaded', [$this, 'handleVerifyOTP']);
		add_action(
			'wilcity/wilcity-shortcodes/default-sc/wilcity-custom-login/before/socials-login',
			[$this, 'renderOTPLoginFormBtn']
		);
		add_action(
			'wilcity/wilcity-shortcodes/default-sc/wilcity-custom-login/form/wilcity_otp_login',
			[$this, 'renderOTPLoginForm']
		);
	}

	public function renderOTPLoginFormBtn()
	{
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'otp_login') {
			return false;
		}
		?>
        <a class="wil-btn mb-10 wil-btn--gradient wil-btn--md wil-btn--round wil-btn--block"
           href="<?php echo esc_url(add_query_arg(['action' => $this->action], GetSettings::getCustomLoginPage())) ?>">
			<?php esc_html_e('OTP Login', 'wiloke-otp-login'); ?></a>
		<?php
	}

	public function handleVerifyOTP()
	{
		$this->getCurrentStep();

		if (!isset($_POST['form_type']) || $_POST['form_type'] !== $this->formType) {
			return false;
		}

		if (!isset($_REQUEST['action']) || $_REQUEST['action'] != $this->action) {
			return false;
		}

		if (isset($_REQUEST['step'])) {

			switch ($_REQUEST['step']) {
				case 'sendOTPStep':
					$this->aVerificationResponse = apply_filters(
						'wiloke-otp-login/filter/Controllers/HandleController/maybeSendOTP',
						[],
						$_POST['user_login']
					);

					Session::setSession('otp-email', $_POST['user_login']);
					$this->currentStep = $this->getNextStep($this->currentStep);
					break;
				case 'resendOTP':
					$this->aVerificationResponse = apply_filters(
						'wiloke-otp-login/filter/Controllers/HandleController/maybeSendOTP',
						[],
						Session::getSession('otp-email')
					);
					$this->currentStep = $this->getNextStep($this->currentStep);
					break;

				case 'verifyOtpStep':
					$this->aVerificationResponse = apply_filters(
						'wiloke-otp-login/filter/Controllers/HandleController/verifyOTPCode',
						[],
						$_POST['otpCode']
					);

					if ($this->aVerificationResponse['status'] == 'success') {
						wp_set_auth_cookie($this->aVerificationResponse['userId'], true, is_ssl());
						if (isset($_GET['redirect_to'])) {
							wp_safe_redirect(urldecode($_GET['redirect_to']));
							exit;
						} else {
							$this->aVerificationResponse = apply_filters(
								'wilcity/wiloke-listing-tools/filter/logged-in/redirection',
								[],
								new \WP_User($this->aVerificationResponse['userId'])
							);

							wp_safe_redirect($this->aVerificationResponse['redirectTo']);
							exit;
						}
					}

					$this->currentStep = 'verifyOtpStep';
					break;
			}

			return $this->aVerificationResponse;
		}
	}

	private function getUrl($aArgs = [])
	{
		$aArgs = wp_parse_args(
			['action' => $this->action],
			$aArgs
		);

		return add_query_arg(
			$aArgs,
			GetSettings::getCustomLoginPage(),
			$this->action
		);
	}

	private function getCurrentStep()
	{
		if (isset($_GET['step'])) {
			if ($_GET['step'] == 'resendOTP') {
				$this->currentStep = 'verifyOtpStep';
			} else {
				$this->currentStep = $_GET['step'];
			}
		} else {
			$this->currentStep = 'sendOTPStep';
		}
	}

	private function getNextStep($currentStep)
	{
		$aFields = include WILOKE_OTP_CONFIGS . 'otp-login.php';
		$this->nextStep = isset($aFields['steps'][$currentStep]) ? $aFields['steps'][$currentStep] : $currentStep;
		return $this->nextStep;
	}

	public function renderSubmitFormBtnName()
	{
		return $this->currentStep == 'verifyOtpStep' ? esc_html__('Verity OTP Code', 'wiloke-otp-login') : esc_html_e
		('Send OTP', 'wiloke-otp-login');
	}

	public function renderOTPLoginForm()
	{
		$aFields = include WILOKE_OTP_CONFIGS . 'otp-login.php';
		if (isset($this->aVerificationResponse['status'])) {
			WilokeMessage::message([
				'status'       => $this->aVerificationResponse['status'] === 'error' ? 'danger' : 'success',
				'hasRemoveBtn' => false,
				'hasMsgIcon'   => false,
				'msgIcon'      => 'la la-envelope-o',
				'msg'          => $this->aVerificationResponse['msg']
			]);
		}
		?>
        <form id="otpform"
              action="<?php echo esc_url($this->getUrl()); ?>"
              method="post"
              novalidate="novalidate">
			<?php
			foreach ($aFields[$this->currentStep] as $aField) {
				HTML::renderDynamicField($aField);
			}

			HTML::renderHiddenField([
				'name'  => 'action',
				'value' => $this->action
			]);

			HTML::renderHiddenField([
				'name'  => 'step',
				'value' => $this->currentStep
			]);

			HTML::renderHiddenField([
				'name'  => 'form_type',
				'value' => $this->formType
			]);
			?>
            <button type="submit"
                    class="wil-btn mb-10 mt-20 wil-btn--gradient wil-btn--md wil-btn--round wil-btn--block">
				<?php echo $this->renderSubmitFormBtnName(); ?></button>
        </form>
		<?php
		if ($this->currentStep == 'verifyOtpStep') {
			?>
            <a href="<?php echo esc_url($this->getUrl(['step' => 'resendOTP'])); ?>"
               class="wil-btn mb-10 mt-10 wil-btn--gradient wil-btn--md wil-btn--round wil-btn--block">
				<?php esc_html_e('Resend OTP', 'wiloke-otp-login'); ?></a>
			<?php
		}
		?>
        <div class="o-hidden ws-nowrap">
            <a class="wil-float-right td-underline"
               href="<?php echo esc_url(add_query_arg(['action' => 'wilcity_login'],
                   GetSettings::getCustomLoginPage())); ?>"><?php esc_html_e('Login With Password',
                    'wiloke-otp-login'); ?></a>
        </div><?php
	}
}
