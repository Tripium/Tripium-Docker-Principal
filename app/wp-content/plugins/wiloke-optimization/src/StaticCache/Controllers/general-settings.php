<form method="post" action="<?php echo $this->getTabUrl('general_settings'); ?>">
	<?php settings_errors('general_static_page_settings'); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Local Directory', 'wiloke-optimization'); ?></th>
            <td>
                <input type="text" class="regular-text code" name="wiloke_static_cache[local_directory]"
                       value="<?php echo esc_attr($this->getField('local_directory')); ?>"/>
                <p class="description"><?php _e('This is the directory where your Static Pages will be saved. The directory must exist and be writeable by the webserver. EG: /wilcity-cache/',
						'wiloke-optimization'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Enable Static Pages for custom post types', 'wiloke-optimization'); ?></th>
            <td>
				<?php
				foreach ($this->getPostTypes() as $postType) {
					?>
                    <p>
                        <label>
                            <input type="checkbox" class="regular-text code"
                                   name="wiloke_static_cache[allowed_post_types][]"
                                   value="<?php echo esc_attr($postType); ?>"
								<?php checked($this->isAllowedStaticFiles($postType), 1) ?>
                            />
							<?php echo esc_html($postType); ?>
                        </label>
                    </p>
					<?php
				}
				?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Exclude the links contain', 'wiloke-optimization'); ?></th>
            <td>
                <textarea cols="100"
                          rows="10"
                          name="wiloke_static_cache[exclude_urls]"><?php echo esc_textarea($this->getField('exclude_urls', $this->getDefaultExcludeRules(), true)); ?></textarea>
                <p class="description"><?php _e('Each rule is separated by a comma.', 'wiloke-optimization'); ?></p>
                <p class="description"><?php _e('addlisting,reset-password', 'wiloke-optimization'); ?></p>
            </td>
        </tr>
    </table>
    <p class="submit">
		<?php echo get_submit_button(null, 'primary large', 'submit', false); ?>


    </p>
</form>
