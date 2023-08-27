<form method="POST" action="<?php echo admin_url('admin.php?page=' . $this->getAuthSlug()); ?>">
    <?php wp_nonce_field('wookit-auth-action', 'wookit-auth-field'); ?>
    <table class="form-table">
        <thead>
        <tr>
            <th><?php echo esc_html__('Wookit Auth Settings', 'wookit'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>
                <label for="wookit-username"><?php echo esc_html__('Username', 'wookit'); ?></label>
            </th>
            <td>
                <input id="wookit-username" type="text" name="wookitAuth[username]"
                       value="<?php echo esc_attr($this->aOptions['username']); ?>" required class="regular-text"/>
            </td>
        </tr>
        <tr>
            <th><label for="wookit-app-password"><?php echo esc_html__('Application Password',
                        'wookit'); ?></label>
            </th>
            <td>
                <input id="wookit-app-password" type="password" name="wookitAuth[app_password]"
                       value="<?php echo esc_attr($this->aOptions['app_password']); ?>" required class="regular-text"/>
            </td>
        </tr>
        </tbody>
    </table>
    <button id="button-save" class="button button-primary" type="submit"><?php esc_html_e('Save Changes',
            'wookit'); ?></button>
</form>
<?php if (!empty(get_option('wookit_purchase_code'))): ?>
    <button id="btn-Revoke-Purchase-Code" class="button button-primary" style="margin-top: 20px;background-color:
    red"><?php esc_html_e
        ('Revoke Purchase Code',
            'wookit'); ?></button>
<?php endif; ?>