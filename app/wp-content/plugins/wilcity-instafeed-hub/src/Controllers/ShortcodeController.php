<?php

namespace WilokeInstagramFeedhub\Controllers;

use WILCITY_SC\SCHelpers;
use WilcityOpenTable\Helpers\Opentable;
use WilokeInstagramFeedhub\Helpers\InstafeedHub;
use WilokeInstagramFeedhub\Helpers\Option;

class ShortcodeController
{
	public function __construct()
	{
		add_shortcode('wilcity_sidebar_instafeedhub', [$this, 'renderShortcode']);
	}

	public function renderShortcode($aAtts)
	{
		$thePost = '';
		if (isset($aAtts['is_mobile'])) {
			$thePost = get_post($aAtts['post_id']);
		}

		if (!InstafeedHub::getInstaId($thePost)) {
			return '';
		}

		$aInstaSettings = Option::getInstaSettings(InstafeedHub::$instaId);

		if (empty($aInstaSettings)) {
			return '';
		}

		if (isset($aAtts['is_mobile'])) {
			return json_encode([
				'slot_data_id' => $aInstaSettings['id']
			]);
		}

		$aGeneralSettings = SCHelpers::decodeAtts($aAtts['atts']);
		ob_start();
		?>
        <div class="<?php echo esc_attr(apply_filters('wilcity/filter/class-prefix',
			'wilcity-sidebar-item-instafeed-hub content-box_module__333d9')); ?>">
			<?php wilcityRenderSidebarHeader($aGeneralSettings['name'], $aGeneralSettings['icon']); ?>
            <div class="content-box_body__3tSRB">
                <div class="wil-instagram-shopify" data-id="<?php echo esc_attr(InstafeedHub::$instaId); ?>"></div>
            </div>
        </div>

		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
