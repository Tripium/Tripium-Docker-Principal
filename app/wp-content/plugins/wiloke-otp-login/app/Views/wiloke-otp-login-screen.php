<?php
$redirectTo = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : admin_url();
login_header(esc_html__('Email Login Screen', 'wiloke-otp-login'));
if (array_key_exists('wilokeotp', $_REQUEST)) :
	if (!empty($this->errMessage)):?>
        <div id="login_error"><?php echo esc_html__($this->errMessage, 'wiloke-otp-login'); ?></div>
	<?php endif;
endif; ?>
<form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>"
      method="post">
    <input type="hidden" name="log" value="<?php echo esc_attr($_REQUEST['log']); ?>"/>
    <input type="hidden" name="pwd" value="<?php echo esc_attr($_REQUEST['pwd']); ?>"/>
    <input type="hidden" name="wp-submit" value="<?php echo esc_attr($_REQUEST['wp-submit']); ?>"/>
	<?php if (array_key_exists('rememberme', $_REQUEST) && 'forever' === $_REQUEST['rememberme']): ?>
        <input name="rememberme" type="hidden" id="rememberme" value="forever"/>
	<?php endif; ?>
    <p>
        <label><?php echo __('Email OTP code', 'wiloke-otp-login'); ?>
            <input type="text" name="wilokeotp" id="wilokeotp" class="input" value="" size="20" autocomplete="off"/>
        </label>
    </p>
    <p><?php esc_html_e('Please enter the OTP code that send to your email', 'wiloke-otp-login'); ?></p>
    <p class="submit">
        <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large"
               value="<?php esc_attr_e('Log In'); ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirectTo); ?>"/>
        <input type="hidden" name="testcookie" value="1"/>
    </p>
<?php
login_footer();