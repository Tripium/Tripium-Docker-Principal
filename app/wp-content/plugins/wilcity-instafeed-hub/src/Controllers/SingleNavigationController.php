<?php

namespace WilokeInstagramFeedhub\Controllers;

use Mpdf\Tag\Ins;
use WilcityServiceClient\Helpers\PremiumPlugin;
use WilokeInstagramFeedhub\Helpers\App;
use WilokeInstagramFeedhub\Helpers\InstafeedHub;
use WilokeInstagramFeedhub\Helpers\Option;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Submission;

class SingleNavigationController
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueInstaSettingsToHead']);
		add_filter(
			'instafeedhub/filter/src/Helpers/Option/getInstaSettingsByPostId/my-insta-settings',
			[$this, 'mergeWilcityInstagramSettingsToInstafeedHub']
		);
		add_filter(
			'wilcity/wiloke-listing-tools/filter/configs/listing-settings/navigation/draggable',
			[$this, 'addItemToNavigation']
		);
		add_filter(
			'wilcity/wiloke-listing-tools/filter/configs/single-nav/fields/sections',
			[
				$this,
				'addNavigationSettings'
			]
		);

		add_action('wilcity/single-listing/home-sections/instafeedhub', [$this, 'renderInstagramToHomepage']);

		// Mobile App
		add_filter(
			'wilcity/filter/wilcity-mobile-app/app/Controllers/Listing/ListingSkeleton/isContentExists/instafeedhub',
			[$this, 'isInstafeedHubExists'],
			10,
			3
		);
		add_filter(
			'wilcity/filter/wilcity-mobile-app/app/controller/json-skeleton/get-navigation-and-home',
			[
				$this, 'addDirectPrintContentToApp'
			]
		);

		add_filter('wilcity/filter/wiloke-listing-tools/app/Framework/Helpers/PostSkeleton/aListing',
			[
				$this, 'getAllInstafeedOfListing'
			],
			10,
			2
		);

		add_filter(
			'wilcity/wilcity-mobile-app/listing/navigation',
			[
				$this,
				'addInstaSlotIdToAppNavigation'
			],
			10,
			2
		);
	}

	public function temporaryDisableFromApp($aItems)
	{
		unset($aItems['instafeedhub']);
		return $aItems;
	}

	public function getInstafeedHub($response, $aData, $post)
	{
		$instaId = InstafeedHub::getInstaId($post);
		$aSettings = InstafeedHub::getInstaSettings($instaId);
		$aResponse = InstafeedHub::fetchInstaItems(
			$aSettings['insta_username'],
			$post->post_author,
			['postsPerPage' => 8]
		);
		if ($aResponse['status'] == 'error') {
			return false;
		}
	}

	public function hasInstafeedHubId($status, $aSection, $post)
	{
		$instaId = InstafeedHub::getInstaId($post);
		if (empty($instaId)) {
			return false;
		}

		$aInstaSettings = InstafeedHub::getInstaSettings($instaId);
		return !empty($aInstaSettings);
	}

	public function addNavigationSettings($aItems)
	{
		$aItems = array_merge($aItems, App::get('configs/navigation')['settings']);

		return $aItems;
	}

	public function addItemToNavigation($aItems)
	{
		return array_merge($aItems, App::get('configs/navigation')['default']);
	}

	public function renderInstagramToHomepage($wilcityArgs)
	{
		if ($wilcityArgs['isShowOnHome'] == 'no') {
			return '';
		}

		global $post;
		$planID = GetSettings::getListingBelongsToPlan($post->ID);
		if (!empty($planID) && !Submission::isPlanSupported($planID, $wilcityArgs['key'])) {
			return false;
		}

		if (!InstafeedHub::getInstaId()) {
			return '';
		}

		$aInstaSettings = InstafeedHub::getInstaSettings(InstafeedHub::$instaId);
		if (empty($aInstaSettings)) {
			return false;
		}

		?>
        <div class="content-box_module__333d9 wil-instafeedhub-wrapper <?php echo esc_attr(apply_filters('wilcity/filter/class-prefix',
			'wilcity-single-listing-content-box')); ?>">
			<?php get_template_part('single-listing/home-sections/section-heading'); ?>
            <div class="content-box_body__3tSRB">
                <div class="wil-instagram-shopify" data-id="<?php echo esc_attr(InstafeedHub::$instaId); ?>"></div>
            </div>
        </div>
		<?php
	}

	public function mergeWilcityInstagramSettingsToInstafeedHub($aInstaSettings)
	{
		if (!InstafeedHub::getInstaId()) {
			return $aInstaSettings;
		}

		$aWilcityInstaSettings = Option::getInstaSettings(InstafeedHub::$instaId);
		if (empty($aWilcityInstaSettings)) {
			return $aInstaSettings;
		}

		if (isset($aInstaSettings[$aWilcityInstaSettings['id']])) {
			return $aInstaSettings;
		}

		$aInstaSettings[] = $aWilcityInstaSettings;

		return $aInstaSettings;
	}

	public function addDirectPrintContentToApp($aSection)
	{
		$aSection['isAppDirectPrint'] = true;
		return $aSection;
	}

	public function isInstafeedHubExists($status, $aSectionInfo, \WP_Post $post)
	{
		if (!InstafeedHub::getInstaId($post)) {
			return false;
		}

		$aInstaSettings = Option::getInstaSettings(InstafeedHub::$instaId);
		return !empty($aInstaSettings);
	}

	public function enqueueInstaSettingsToHead()
	{
		if (!InstafeedHub::getInstaId()) {
			return '';
		}

		$aInstaSettings = Option::getInstaSettings(InstafeedHub::$instaId);

		if (empty($aInstaSettings)) {
			return false;
		}

		if (!isset($aInstaSettings['display_on_pages'])) {
			$aInstaSettings['display_on_pages'] = [];
		}

		if (defined('IFH_URL')) {
			wp_enqueue_script('callback-instafeedhub', WILCITY_INSTAFEED_HUB_URL . 'assets/js/script.js',
				['instafeedhub'],
				WILOKE_INSTAFEEDHUB_VERSION, true);
		} else {
			wp_enqueue_style('instafeedhub', 'https://instafeedhub-layout.netlify.app/styles.css', [],
				WILOKE_INSTAFEEDHUB_VERSION);
			wp_enqueue_script('instafeedhub', 'https://instafeedhub-layout.netlify.app/main.js', [],
				WILOKE_INSTAFEEDHUB_VERSION,
				true);
			wp_enqueue_script('callback-instafeedhub', WILCITY_INSTAFEED_HUB_URL . 'assets/js/script.js',
				['instafeedhub'],
				WILOKE_INSTAFEEDHUB_VERSION, true);
			wp_localize_script('instafeedhub', '__wilInstagramShopify__', [$aInstaSettings]);
		}
	}

	public function addInstaSlotIdToAppNavigation($aNavigation, $post)
	{
		if (!isset($aNavigation['instafeedhub'])) {
			return $aNavigation;
		}

		if (!InstafeedHub::getInstaId($post)) {
			return $aNavigation;
		}

		$instaId = InstafeedHub::$instaId;
		settype($instaId, 'string');

		$aNavigation['instafeedhub']['content'] = [
			'slot_data_id' => $instaId
		];
		return $aNavigation;
	}

	public function getAllInstafeedOfListing($aListingSkeleton, $post)
	{
		if (!InstafeedHub::getInstaId($post)) {
			$aListingSkeleton['instafeedhub'] = [];
			return $aListingSkeleton;
		}

		$aInstaSettings = Option::getInstaSettings(InstafeedHub::$instaId);


		if (empty($aInstaSettings)) {
			$aListingSkeleton['instafeedhub'] = [];
			return $aListingSkeleton;
		}

		$aListingSkeleton['instafeedhub'] = [$aInstaSettings];
		return $aListingSkeleton;
	}

	public function addDeferToScript($url)
	{
		if (FALSE === strpos($url, 'instafeedhub-layout.netlify.app/main.js')) { // not our file
			return $url;
		}
		// Must be a ', not "!
		return "$url' defer='defer";
	}
}
