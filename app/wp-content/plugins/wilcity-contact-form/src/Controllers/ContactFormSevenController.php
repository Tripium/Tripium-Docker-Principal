<?php

namespace WilcityContactForm\Controllers;

use WilokeListingTools\Framework\Helpers\General;

class ContactFormSevenController extends Controller
{
	public function __construct()
	{
		add_filter('wpcf7_mail_components', [$this, 'modifyReceipt'], 10, 3);
		add_filter('wpcf7_form_hidden_fields', [$this, 'addHiddenFieldsToContactForm'], 10, 1);
		add_filter(
			'wilcity/filter/wilcity-mobile-app/app/Controllers/Listing/ListingSkeleton/getSCContent/contactForm7',
			[
				$this, 'renderContactFormOnTheAppSidebar'
			],
			10,
			2
		);
	}

	/**
	 * @param $val
	 * @param $aSection
	 * @return string
	 */
	public function renderContactFormOnTheAppSidebar($val, $aSection): string
	{
		if (empty($val)) {
			return $val;
		}

		return apply_filters(
			'wilcity/filter/wilcity-contact-form/Controllers/ContactFormSevenController/renderContactFormOnTheAppSidebar',
			$val,
			$aSection
		);
	}

	/**
	 * @param $aFields
	 *
	 * @return mixed
	 */
	public function addHiddenFieldsToContactForm($aFields)
	{
		global $post;
		if (!isset($post->post_type) || !General::isPostTypeSubmission($post->post_type)) {
			return $aFields;
		}

		$aFields['_wilcity_current_post_id'] = $post->ID;

		return $aFields;
	}

	/**
	 * @param $components
	 * @param $currentContactForm
	 * @param $that
	 *
	 * @return mixed
	 */
	public function modifyReceipt($components, $currentContactForm, $that)
	{
		if (isset($_POST['_wilcity_current_post_id']) && !empty($_POST['_wilcity_current_post_id'])) {
			$email = $this->getListingAuthorEmail(absint($_POST['_wilcity_current_post_id']));
			if (!empty($email)) {
				$components['recipient'] = $email;
			}
		}
		return $components;
	}
}
