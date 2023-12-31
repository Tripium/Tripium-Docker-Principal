<?php

use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;
use WILCITY_SC\SCHelpers;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Frontend\SingleListing;

add_shortcode('wilcity_sidebar_business_info', 'wicitySidebarBusinessInfo');

function wilcitySidebarBusinessInfoAreSocialsEmpty($aSocialNetworks)
{
	if (empty($aSocialNetworks)) {
		return true;
	}
	foreach ($aSocialNetworks as $icon => $link) {
		if (!empty($link)) {
			return false;
		}
	}

	return true;
}

function wicitySidebarBusinessInfo($aArgs)
{
	global $post;
	$aAtts = SCHelpers::decodeAtts($aArgs['atts']);
	$aAtts = wp_parse_args(
		$aAtts,
		[
			'name'         => esc_html__('Business Info', WILOKE_LISTING_DOMAIN),
			'icon'         => 'la la-qq',
			'desc'         => '',
			'currencyIcon' => 'la la-dollar'
		]
	);

	$itemWrapperClass = 'mt-20 mt-sm-15';
	$address = GetSettings::getAddress($post->ID, false);
	$email = GetSettings::getListingEmail($post->ID);
	$phone = GetSettings::getListingPhone($post->ID);
	$website = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_website') ? '' :
		GetSettings::getPostMeta($post->ID, 'website');
	$aSocialNetworks = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_social_networks') ? '' :
		GetSettings::getPostMeta($post->ID, 'social_networks');
	$wrapperClass = apply_filters(
		'wilcity/filter/class-prefix',
		'wilcity-sidebar-item-business-info content-box_module__333d9'
	);

	$aSocialNetworks = apply_filters(
		'wilcity/filter/wilcity-shortcodes/sidebar-business-info/social-networks',
		$aSocialNetworks,
		$aAtts
	);

	if (isset($aAtts['isMobile'])) {
		return apply_filters('wilcity/mobile/sidebar/business_info', $post, $aAtts, [
			'socialNetworks' => $aSocialNetworks,
			'email'          => $email,
			'phone'          => $phone,
			'address'        => $address,
		]);
	}

	if (!empty($address) || !empty($phone) || !empty($email) || !empty($website) ||
		!wilcitySidebarBusinessInfoAreSocialsEmpty($aSocialNetworks)) :
		ob_start();
		?>
        <div class="<?php echo esc_attr($wrapperClass); ?>">
			<?php wilcityRenderSidebarHeader($aAtts['name'], $aAtts['icon']); ?>
            <div class="content-box_body__3tSRB">

				<?php
				$aInfo = apply_filters('wilcity/sidebar/business_info/order_show',
					['email', 'phone', 'website', 'address', 'social', 'inbox']);
				foreach ($aInfo as $name) {
					switch ($name) :
						case 'address':
							if (!empty($address)) {
								SCHelpers::renderIconAndLink($address, 'la la-map-marker', $address, [
									'wrapperClass'     => $itemWrapperClass . ' text-pre wil-listing-address',
									'isGoogle'         => true,
									'iconWrapperClass' => 'rounded-circle'
								]);
							}
							break;
						case 'phone':
							if (!empty($phone) &&
								SingleListing::isClaimedListing($post->ID)) {
								SCHelpers::renderIconAndLink($phone, 'la la-phone', $phone, [
									'wrapperClass'     => $itemWrapperClass . ' wil-listing-phone',
									'isPhone'          => true,
									'iconWrapperClass' => 'rounded-circle'
								]);
							}
							break;
						case 'email':
							if (!empty($email) &&
								SingleListing::isClaimedListing($post->ID)) {
								SCHelpers::renderIconAndLink($email, 'la la-envelope', $email, [
									'wrapperClass'     => $itemWrapperClass . ' wil-listing-email',
									'isEmail'          => true,
									'iconWrapperClass' => 'rounded-circle'
								]);
							}
							break;
						case 'website':
							if ($website) {
								SCHelpers::renderIconAndLink($website, 'la la-globe', $website, [
									'wrapperClass'     => $itemWrapperClass . ' wil-listing-website',
									'iconWrapperClass' => 'rounded-circle'
								]);
							}
							break;
						case 'social':
							if (!empty($aSocialNetworks)):
								?>
                                <div class="icon-box-1_module__uyg5F mt-20 mt-sm-15">
                                    <div class="social-icon_module__HOrwr social-icon_style-2__17BFy">
										<?php
										foreach ($aSocialNetworks as $icon => $link) :
											if (empty($link)) {
												continue;
											}
											if ($icon == 'whatsapp') {
												$link = General::renderWhatsApp($link);
											}

											switch ($icon) {
												case 'wikipedia':
													$icon = 'fa fa-wikipedia-w';
													break;
												case 'bloglovin':
													$icon = 'fa fa-heart';
													break;
												case 'line':
													$icon = 'la la-line';
													break;
												default:
													$icon = 'fa fa-' . $icon;
													break;
											}

											$aNoFollows = WilokeThemeOptions::getOptionDetail('nofollow');
											if (empty($aNoFollows) || (is_array($aNoFollows)) && in_array('clientwebsite', $aNoFollows)) {
												?>
                                                <a class="social-icon_item__3SLnb" href="<?php echo esc_url($link); ?>"
                                                   target="_blank" rel="nofollow">
                                                    <i class="<?php echo esc_attr($icon); ?>"></i>
                                                </a>
											<?php } else {
												?>
                                                <a class="social-icon_item__3SLnb" href="<?php echo esc_url($link); ?>"
                                                   target="_blank">
                                                    <i class="<?php echo esc_attr($icon); ?>"></i>
                                                </a>
												<?php
											}
										endforeach;
										?>
                                    </div>
                                </div>
							<?php
							endif;
							break;
						case 'inbox':
							if (SingleListing::isClaimedListing($post->ID)): ?>
                                <wil-message-btn btn-name="<?php esc_html_e('Inbox', 'wilcity-shortcodes'); ?>"
                                                 wrapper-classes="wilcity-inbox-btn wil-btn wil-btn--block mt-20
                                                 wil-btn--border wil-btn--round wil-listing-inbox"
                                                 :receiver-id="<?php echo abs($post->post_author); ?>"
                                                 receiver-name="<?php echo User::getField('display_name',
									                 $post->post_author); ?>"
                                ></wil-message-btn>
							<?php endif;
							break;
					endswitch;
				}
				?>
				<?php do_action('wilcity/wilcity-shortcodes/wilcity-sidear-business-info/after-info', $post); ?>
            </div>
        </div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
	else:
		$content = '';
	endif;

	return $content;
}
