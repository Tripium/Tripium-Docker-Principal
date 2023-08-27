<?php


namespace WilcityAppDeepLink\Controllers;


use WILCITY_APP\Helpers\App;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;

class SingleController
{
	private $schema;
	private $aCacheDeepLinkInfo = [];

	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('wilcity/after-open-body', [$this, 'addOpenInAppButton']);
	}

	protected function isEnableOpenInApp()
	{
		if (is_singular() && wp_is_mobile()) {
			if (\WilokeThemeOptions::isEnable('mobile_app_toggle_deep_link', false)) {
				$this->schema = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_scheme');
				if (empty($this->schema)) {
					return false;
				}

				$this->schema = $this->schema . '://';
				return true;
			}
		}

		return false;
	}

	public function addOpenInAppButton()
	{
		global $post;

		if (!$this->isEnableOpenInApp() || !isset($post->ID) || !$this->getSingleDeepLink($post)) {
			return false;
		}

		$title = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_title');
		$desc = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_desc', 'Open In App');
		$icon = \WilokeThemeOptions::getThumbnailUrl('mobile_app_deep_link_icon');
		$bgColor = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_bg_color');
		$descBgColor = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_desc_bg_color', '#399');
		$descTextColor = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_desc_text_color', '#fff');
		$titleTextColor = \WilokeThemeOptions::getOptionDetail('mobile_app_deep_link_title_text_color', '#000');

		?>
		<?php if (!empty($bgColor)) : ?>
        <div class="open-wilcity-application"
        style="background-color: <?php echo esc_attr($bgColor); ?>; padding: 10px">
	<?php else: ?>
        <div class="open-wilcity-application" style="padding: 10px">
	<?php endif; ?>
        <div class="content" style="display:flex;flex-direction:row;align-items:center;">
			<?php if (!empty($icon)) : ?>
                <img width="70" height="70"
                     src="<?php echo esc_url($icon); ?>"
                     alt="<?php echo esc_attr($title); ?>">
			<?php endif; ?>
            <div style="margin:0 25px 0 15px;">
				<?php if (!empty($title)): ?>
                    <h2 style="font-size:16px;margin:0 0 4px; color: <?php echo esc_attr($titleTextColor); ?>"><?php
						echo esc_html
						($title);
						?></h2>
				<?php endif; ?>
				<?php if (!empty($desc)): ?>
                    <span id="wil-open-in-app-text" style="background-color:<?php echo esc_attr($descBgColor); ?>;
                            display:inline-block;
                            padding:2px 10px;border-radius:5px;
                            color:<?php echo esc_attr($descTextColor); ?>;font-size:14px;"><?php echo esc_html($desc); ?></span>
				<?php endif; ?>
            </div>
        </div>
        <span class="close"
              style="position:absolute;top:0;right:5px;font-size:30px;display:inline-block;padding:5px;">&times;</span>
        </div>
		<?php
	}

	protected function getListingDeepLink($post): array
	{
		$vrSrc = GetSettings::getPostMeta($post->ID, 'vr_src');

		return [
			'screen'    => 'listing-detail',
			'id'        => $post->ID,
			'image'     => get_the_post_thumbnail_url($post->ID, 'large'),
			'postTitle' => (get_the_title($post->ID)),
			'tagline'   => (GetSettings::getTagLine($post)),
			'link'      => get_permalink($post->ID),
			'logo'      => GetSettings::getLogo($post->ID),
			'author'    => [
				'userID'      => abs($post->post_author),
				'avatar'      => User::getAvatar($post->post_author),
				'displayName' => User::getField('display_name', $post->post_author)
			],
			'header'    => [
				'coverImg' => GetSettings::getCoverImage($post->ID),
				'vrSrc'    => empty($vrSrc) ? '' : $vrSrc
			],
			'coverImg'  => GetSettings::getCoverImage($post->ID)
		];
	}

	protected function getEventDeepLink($post)
	{
		$aInfo = App::get('EventGeneralData')->getData(
			$post,
			['id', 'postTitle', 'featuredImage', 'address', 'oAddress', 'hostedBy', 'oFavorite']
		);

		$aInfo['image'] = $aInfo['featuredImage'];
		$aInfo['address'] = $aInfo['oAddress'];

		if (!empty($aInfo['hostedBy'])) {
			$aInfo['hosted'] = wilcityAppGetLanguageFiles('hostedBy') . ' ' . $aInfo['hostedBy']['name'];
		} else {
			$aInfo['hosted'] = '';
		}

		if (!empty($aInfo['oAddress'])) {
			$aInfo['address'] = $aInfo['oAddress']['address'];
		} else {
			$aInfo['address'] = '';
		}

		if (!empty($aInfo['oFavorite']['totalFavorites'])) {
			$aInfo['interested'] = $aInfo['oFavorite']['totalFavorites'] . ' ' .
				wilcityAppGetLanguageFiles('peopleInterested');
		} else {
			$aInfo['interested'] = '';
		}
		$aInfo['screen'] = 'event-detail';

		unset($aInfo['oAddress']);
		unset($aInfo['featuredImage']);
		unset($aInfo['hostedBy']);
		unset($aInfo['oFavorite']);
		unset($aInfo['isEnableDiscussion']);
		unset($aInfo['menuOrder']);
		return $aInfo;
	}

	protected function getProductDeepLink($post)
	{
		$aInfo = \WilokeListingTools\Framework\Helpers\App::get('ProductSkeleton')->getSkeleton(
			$post->ID,
			[
				'ID',
				'featuredImage',
				'name'
			]
		);

		$aInfo['productID'] = $aInfo['ID'];
		unset($aInfo['ID']);
		$aInfo['screen'] = 'product-detail';
		$aInfo['featuredImg'] = $aInfo['featuredImage'];
		unset($aInfo['featuredImage']);

		return $aInfo;
	}

	protected function getSingleDeepLink($post)
	{
		if (isset($this->aCacheDeepLinkInfo[$post->ID])) {
			return $this->aCacheDeepLinkInfo[$post->ID];
		}

		if ($post->post_type == 'product') {
			return $this->getProductDeepLink($post);
		}

		if (General::isPostTypeInGroup($post->post_type, 'listing', '')) {
			return $this->getListingDeepLink($post);
		}

		if (General::isPostTypeInGroup($post->post_type, 'event', '')) {
			return $this->getEventDeepLink($post);
		}

		return false;
	}

	protected function isOpenInApp()
	{
		$url = (is_ssl() ? "https://" : "http://") . "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		return strpos($url, '#isOpenInApp') !== false ? 'yes' : 'no';
	}

	public function enqueueScripts()
	{
		if (!$this->isEnableOpenInApp() || !defined('WILCITY_MOBILE_APP')) {
			return false;
		}

		global $post;
		$aDeepLink = apply_filters(
			'wilcity/filter/wilcity-app-deep-link/Controllers/SingleController/enqueueScripts',
			$this->getSingleDeepLink($post),
            $post
		);

		$this->aCacheDeepLinkInfo[$post->ID] = $aDeepLink;
		if (empty($aDeepLink)) {
			return false;
		}

		foreach ($aDeepLink as $key => $val) {
			$aDeepLink[$key] = is_string($val) ? rawurlencode($val) : $val;
		}

		if (!apply_filters('wilcity/filter/wilcity-app-deep-link/src/Controller/SingleController/isEnableOpenInApp',
			true, $aDeepLink, $post)) {
			return false;
		}

		wp_register_script('wilcity-app-deep-link', WILCITY_APP_DEE_LINK_URL . 'source/js/script.js', [],
			WILCITY_APP_DEE_LINK_VERSION, true);
		wp_localize_script('wilcity-app-deep-link', 'WILCITY_APP_DEEP_LINK', [
			'androidPackage' => \WilokeThemeOptions::getOptionDetail('mobile_app_android_package'),
			'isOSAppId'      => \WilokeThemeOptions::getOptionDetail('mobile_app_ios_app_id'),
			'deepLink'       => add_query_arg($aDeepLink, $this->schema),
			'lang'           => [
				'checking' => esc_html__('Checking', 'wilcity-app-deep-link')
			]
		]);
		wp_enqueue_script('wilcity-app-deep-link');
	}
}
