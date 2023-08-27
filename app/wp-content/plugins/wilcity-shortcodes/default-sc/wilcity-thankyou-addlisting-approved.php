<?php

use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Models\PaymentMetaModel;


function wilcityAllowToAccessThankYouMessage(): bool
{
	if (isset($_GET["testAction"]) && $_GET["testAction"] === "thankyou_approved") {
		return true;
	}

	if (!isset($_REQUEST['category']) || !in_array($_REQUEST['category'], ['addlisting'])) {
		return false;
	}

	if (!isset($_REQUEST['postID'])) {
		return false;
	}

	$aParsePostIDs = explode(',', $_REQUEST['postID']);

	if (get_post_status($aParsePostIDs[0]) !== 'publish') {
		return false;
	}

	if ($_REQUEST['category'] === 'paidClaim') {
		if (isset($_REQUEST['paymentID'])) {
			$claimID     = PaymentMetaModel::get($_REQUEST['paymentID'], 'claimID');
			$claimStatus = GetSettings::getPostMeta($claimID, 'claim_status');
			if ($claimStatus !== 'approved') {
				return false;
			}
		}
	}

	return true;
}
function wilcityThankyouAddListingApproved($aArgs, $content)
{
    $isAllow = wilcityAllowToAccessThankYouMessage();
	if (!$isAllow) {
		return '';
	}

    return apply_filters('wilcity/thankyou-content', nl2br($content), [
      'postID'      => $_REQUEST['postID'],
      'promotionID' => isset($_REQUEST['promotionID']) ? $_REQUEST['promotionID'] : '',
      'category'    => $_REQUEST['category']
    ]);
}

add_shortcode('wilcity_thankyou_addlisting_approved', 'wilcityThankyouAddListingApproved');
