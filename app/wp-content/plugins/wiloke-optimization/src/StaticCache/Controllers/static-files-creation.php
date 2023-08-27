<div style="margin-top: 30px;" class="wiloke-optimization-wrapper">
    <div style="display: inline-block">
        <button id="wilcity-generate-static-files"
                class="button-primary button"><?php esc_html_e('Generate Static Pages',
				'wiloke-optimization'); ?></button>

        <button id="wilcity-cancel-generate-static-files" class="button-primary disabled button">
			<?php esc_html_e('Cancel', 'wiloke-optimization'); ?>
        </button>
    </div>
    <div style="display: inline-block">
        <form method="post" action="<?php echo $this->getTabUrl('generate_static_files'); ?>">
			<?php
			if (current_user_can('administrator')) {
				if (isset($_POST['action']) && $_POST['action'] == 'delete_static_files') {
					$aResponse = $this->deleteStaticFiles();

					echo '<p>'.$aResponse['msg'].'</p>';
				}
			}
			?>
            <input type="hidden" name="action" value="delete_static_files">
            <button id="wilcity-delete-generate-static-files" class="button-secondary button delete">
				<?php esc_html_e('Delete Static Pages', 'wiloke-optimization'); ?>
            </button>
        </form>
    </div>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Log', 'wiloke-optimization'); ?></th>
            <td>
                <p id="wiloke-static-files-generation-log"></p>
            </td>
        </tr>
    </table>
</div>


