<?php


namespace WilokeInstagramFeedhub\Controllers;


use WilokeInstagramFeedhub\Helpers\App;
use WilokeInstagramFeedhub\Helpers\InstafeedHub;
use WilokeListingTools\Controllers\Retrieve\AjaxRetrieve;
use WilokeListingTools\Controllers\RetrieveController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;

class ListingMetaBoxes extends Controller
{
	public function __construct()
	{
		add_action('cmb2_admin_init', [$this, 'registerInstafeedHub'], 10);
		add_action('wp_ajax_wilcity_admin_search_instafeedhub', [$this, 'adminSearchForInstafeedHub']);
		add_filter('wilcity/filter/wiloke-listing-tools/select2-values', [$this, 'addSelectedOption'], 10, 2);

		add_action('save_post', [$this, 'saveSettingsInWP52'], 10, 3);
		add_action('init', [$this, 'saveSettingsWP53'], 1);
	}

	private function saveInstafeedHub($listingID, $post, $updated)
	{
		$aPostTypeKeys = General::getPostTypeKeys(true, false);

		if (!in_array($post->post_type, $aPostTypeKeys)) {
			return false;
		}

		if (isset($_POST['wilcity_instafeedhub']) && !empty($_POST['wilcity_instafeedhub'])) {
			SetSettings::setPostMeta($listingID, 'wilcity_instafeedhub', $_POST['wilcity_instafeedhub']);
		} else {
			SetSettings::deletePostMeta($listingID, 'wilcity_instafeedhub');
		}
	}

	public function saveSettingsWP53()
	{
		if (!$this->isWP53() || !$this->isSavedPostMeta()) {
			return false;
		}

		$this->saveInstafeedHub($_POST['post_ID'], get_post($_POST['post_ID']), true);
	}

	public function saveSettingsInWP52($listingID, $post, $updated)
	{
		if ($this->isWP53()) {
			return false;
		}

		if (!current_user_can('edit_posts') || !$this->isAdminEditing()) {
			return false;
		}

		$this->saveInstafeedHub($listingID, $post, $updated);
	}

	public function adminSearchForInstafeedHub()
	{
		$oRetrieve = new RetrieveController(new AjaxRetrieve());
		if (isset($_GET['q']) && !empty($_GET['q'])) {
			$aResponse = InstafeedHub::search($_GET['q']);
			if ($aResponse['status'] == 'error') {
				$oRetrieve->error(['msg' => $aResponse['msg']]);
			} else {
				$aResponse['items'] = array_map(function ($aItem) {
					$aItem['text'] = $aItem['title'];
					$aItem['id'] = json_encode(['id' => $aItem['id'], 'name' => $aItem['text']]);
					return $aItem;
				}, $aResponse['items']);

				$oRetrieve->success([
					'msg' => [
						'results' => $aResponse['items']
					]
				]);
			}
		} else {
			$oRetrieve->error(['msg' => esc_html__('Please enter your keyword', 'wilcity-instafeedhub')]);
		}
	}

	public function registerInstafeedHub()
	{
		if (!$this->isCurrentAdminListingType() || $this->isDisableMetaBlock(['fieldKey' => 'instafeedhub'])) {
			return false;
		}

		new_cmb2_box(App::get('configs/metaboxes')['instafeedhub']);
	}

	public function addSelectedOption($aItems, $field)
	{
		if (!isset($_GET['post']) || empty($_GET['post']) || $field->args('id') !== 'wilcity_instafeedhub') {
			return $aItems;
		}

		$item = GetSettings::getPostMeta($_GET['post'], 'wilcity_instafeedhub');
		$aItem = json_decode($item, true);
		return empty($aItem) ? [] : [$aItem];
	}

	public static function getInstafeedHub()
	{
		if (!isset($_GET['post']) || empty($_GET['post'])) {
			return '';
		}

		$item = GetSettings::getPostMeta($_GET['post'], 'wilcity_instafeedhub');
		$aItem = json_decode($item, true);

		return isset($aItem['id']) ? $aItem['id'] : '';
	}
}
