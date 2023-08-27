<form method="POST" action="<?php echo admin_url('admin.php?page=' . $this->slug); ?>">
	<?php wp_nonce_field('wiloke-google-api-action', 'wiloke-google-api-field'); ?>
    <table class="form-table">
        <thead>
        <tr>
            <th><label for="wiloke-google-enable"><?php esc_html_e('Is Enable?', 'wiloke-gmail-login');
			        ?></label>
            </th>
            <td>
                <select name="wiloke-google[enable]" id="wiloke-google-enable">
                    <option value="yes" <?php selected($this->aOptions['enable'], 'yes') ?>>Yes</option>
                    <option value="no" <?php selected($this->aOptions['enable'], 'no') ?>>No</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="wiloke-google-client-id"><?php esc_html_e('Client ID', 'wiloke-gmail-login');
            ?></label>
            </th>
            <td>
                <textarea id="wiloke-google-client-id" name="wiloke-google[client_id]"
                          class="regular-text"><?php echo esc_html($this->aOptions['client_id']); ?></textarea>
                <p><?php esc_html_e('Click here to config', 'wiloke-gmail-login'); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="wiloke-google-client-secret"><?php esc_html_e('Client Secret',
						'wiloke-gmail-login'); ?></label>
            </th>
            <td>
                <textarea id="wiloke-google-client-secret" name="wiloke-google[client_secret]"
                          class="regular-text"><?php echo esc_html($this->aOptions['client_secret']); ?></textarea>
                <p><?php esc_html_e('Click here to config', 'wiloke-gmail-login'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="wiloke-redirect-uri"><?php esc_html_e('Redirect URI', 'wiloke-gmail-login'); ?></label>
            </th>
            <td>
                <input id="wiloke-redirect-uri" type="text" name="wiloke-google[redirect_uri]"
                       value="<?php echo esc_attr($this->aOptions['redirect_uri']); ?>" class="regular-text"/>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="button button-primary" type="submit">Save Changes</button>
</form>

