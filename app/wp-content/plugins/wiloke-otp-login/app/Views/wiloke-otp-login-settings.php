<form method="POST" action="<?php echo admin_url('admin.php?page=' . $this->slug); ?>">
	<?php wp_nonce_field('wiloke-otp-action', 'wiloke-otp-field'); ?>
    <table class="form-table">
        <thead>
        <tr>
            <th><?php echo __('Wiloke OTP Login Settings', 'wiloke-otp-login'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th><label for="wiloke-otp-is-enable"><?php echo __('Is Enable', 'wiloke-otp-login'); ?></label></th>
            <td>
                <select id="wiloke-otp-is-enable" type="text" name="wilokeotp[is_enable]">
                    <option value="no" <?php selected('no', $this->aOptions['is_enable']); ?>><?php echo __('No', 'wiloke-otp-login'); ?></option>
                    <option value="yes" <?php selected('yes', $this->aOptions['is_enable']); ?>><?php echo __('Yes', 'wiloke-otp-login'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label for="wiloke-otp-expiration"><?php echo __('Expiration Time (Mins)', 'wiloke-otp-login'); ?></label>
            </th>
            <td>
                <input id="wiloke-otp-expiration" type="number" name="wilokeotp[expiration_time]" value="<?php echo
				esc_attr($this->aOptions['expiration_time']); ?>" min ="1" max ="60" class="regular-text"/>
            </td>
        </tr>
        <tr>
            <th><label for="wiloke-otp-email-subject"><?php echo __('Email Subject', 'wiloke-otp-login'); ?></label>
            </th>
            <td>
                <input id="wiloke-otp-email-subject" type="text" name="wilokeotp[email_subject]" value="<?php echo
				esc_attr($this->aOptions['email_subject']); ?>" class="regular-text"/>
            </td>
        </tr>
        <tr>
            <th><label for="wiloke-otp-email-content"><?php echo __('Email Content', 'wiloke-otp-login'); ?></label>
            </th>
            <td>
                <textarea id="wiloke-otp-email-content" name="wilokeotp[email_content]" class="regular-text"><?php echo esc_attr($this->getEmailContent()); ?>
                </textarea>
                <p><?php _e('The OTP code is %OTPcode%', 'wiloke-otp-login'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="button button-primary" type="submit">Save Changes</button>
</form>

