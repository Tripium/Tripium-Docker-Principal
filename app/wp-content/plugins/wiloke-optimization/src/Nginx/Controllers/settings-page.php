<div class="wiloke-optimization-wrapper">
    <h2><?php _e('Nginx Cache', 'nginx'); ?></h2>
    <hr>
	<?php settings_errors('nginx_cache'); ?>
    <form method="post" action="<?php echo esc_url($this->getAdminPage()); ?>">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Cache Zone Path', 'wiloke-optimization'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="nginx_cache[path]"
                           placeholder="/data/nginx/cache"
                           value="<?php echo esc_attr($this->getField('path')); ?>"/>
                    <p class="description"><?php _e('The absolute path to the location of the cache zone, specified in the Nginx <code>fastcgi_cache_path</code> or <code>proxy_cache_path</code> directive.',
							'wiloke-optimization'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Purge Cache', 'wiloke-optimization'); ?></th>
                <td>
                    <label for="nginx_auto_purge">
                        <input name="nginx_cache[auto_purge]" type="checkbox" id="nginx_auto_purge"
                               value="1" <?php checked($this->getField('auto_purge'), '1'); ?> />
						<?php _e('Automatically flush the cache when content changes', 'wiloke-optimization'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <p class="submit">
			<?php echo get_submit_button(null, 'primary large', 'submit', false); ?>
            <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'purge-nginx-cache'], $this->getAdminPage()),
				'purge-nginx-cache'); ?>" class="button button-secondary button-large delete<?php if (is_wp_error(
				$this->isValidPath())) : ?> disabled<?php endif; ?>">
				<?php _e('Purge Cache', 'wiloke-optimization'); ?>
            </a>
        </p>
    </form>
</div>
