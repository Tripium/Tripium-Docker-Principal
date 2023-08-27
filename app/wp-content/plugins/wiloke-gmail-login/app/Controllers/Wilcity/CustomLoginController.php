<?php


namespace WilokeGmailLogin\Controllers\Wilcity;


use WilokeGmailLogin\Helpers\Option;

class CustomLoginController
{
	public function __construct()
	{
		add_action(
			'wilcity/wilcity-shortcodes/default-sc/wilcity-custom-login/socials-login',
			[$this, 'maybeAddGoogleLoginBtn']
		);
	}

	public function maybeAddGoogleLoginBtn()
	{
		if (Option::getField('enable') == 'no') {
			return false;
		}
		$redirectTo = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url('/');
		?>
        <gmail-login redirect-to="<?php echo esc_url($redirectTo); ?>"></gmail-login>
		<?php
	}
}
