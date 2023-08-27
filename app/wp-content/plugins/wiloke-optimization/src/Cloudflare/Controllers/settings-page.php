<?php use WilokeOptimization\Cloudflare\Models\LogModel; ?>
<div class="wiloke-optimization-wrapper">
    <h2>CloudFlare</h2>
    <hr/>
    <form method="post" action="<?php echo esc_url($this->getAdminPage()); ?>">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Email</th>
                <td>
                    <input type="text" class="regular-text code" name="cf_cache[email]"
                           value="<?php echo esc_attr($this->getField('email')); ?>"/>
                    <p class="description"><?php _e('Your CloudFlare Email', 'wiloke-optimization'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Global API</th>
                <td>
                    <input type="password" class="regular-text code" name="cf_cache[global_api]"
                           value="<?php echo esc_attr($this->getField('global_api')); ?>"/>
                    <p class="description">
						<?php printf(__('Your CloudFlare Global API. Click <a href="%s" target="_blank">me</a> to know how to get the Global API.',
							'wiloke-optimization'),
							'https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-Cloudflare-API-key-'); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Purge Mode</th>
                <td>
                    <select name="cf_cache[purge_mode]" id="purge_mode">
                        <option value="" <?php selected("", $this->getField('purge_mode')); ?>>
							<?php _e('Disable', 'wiloke-optimization'); ?>
                        </option>
                        <option value="purge_all" <?php selected("purge_all", $this->getField('purge_mode')); ?>>
							<?php _e('Purge Everything', 'wiloke-optimization'); ?>
                        </option>
                        <option value="custom" <?php selected("custom", $this->getField('purge_mode')); ?>>
							<?php _e('Custom', 'wiloke-optimization'); ?>
                        </option>
                    </select>
                    <p>
						<?php _e('Custom mode: It will purge Homepage cache, your changed post cache and the custom urls that are listed below.', 'wiloke-optimization'); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Custom URLs</th>
                <td>
                    <textarea class="regular-text" rows="5" name="cf_cache[custom_urls]"><?php echo esc_textarea
                        ($this->getField('custom_urls')); ?></textarea>
                    <p>
                        <?php _e('Each url is separated by a comma', 'wiloke-optimization'); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Log</th>
                <td>
                <textarea rows="10" cols="100" readonly><?php
	                $aLogs = LogModel::get();
	                if (!empty($aLogs)) {
		                foreach (LogModel::get() as $oLog) {
			                print($oLog->created_at . "\t" . $oLog->log . "\r\n");
		                }
	                }
	                ?></textarea>
                    <br>
                </td>
            </tr>
        </table>

        <p class="submit">
			<?php echo get_submit_button(null, 'primary large', 'submit', false); ?>
            <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'purge-cf-cache'], $this->getAdminPage()),
				'purge-cf-cache'); ?>" class="button button-secondary button-large delete<?php if (is_wp_error(
				$this->isValidConfiguration())) : ?> disabled<?php endif; ?>">
				<?php _e('Purge Cache', 'wiloke-optimization'); ?>
            </a>
        </p>
    </form>
</div>
