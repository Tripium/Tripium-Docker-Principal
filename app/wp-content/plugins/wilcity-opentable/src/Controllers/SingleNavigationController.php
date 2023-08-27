<?php

namespace WilcityOpenTable\Controllers;

use WilcityOpenTable\Helpers\App;
use WilcityOpenTable\Helpers\Opentable;
use WilcityServiceClient\Helpers\PremiumPlugin;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Submission;

class SingleNavigationController
{
	public function __construct()
	{
		add_filter(
			'wilcity/wiloke-listing-tools/filter/configs/listing-settings/navigation/draggable',
			[$this, 'addOpentableToNavigation']
		);
		add_filter(
			'wilcity/wiloke-listing-tools/filter/configs/single-nav/fields/sections',
			[
				$this,
				'addNavigationSettings'
			]
		);

		add_filter(
			'wilcity/wiloke-listing-tools/filter/configs/single-nav/fields/status/conditional/excludes',
			[$this, 'excludeOpenTableFromNav']
		);

		add_action('wilcity/single-listing/home-sections/opentable', [$this, 'render']);
	}

	public function excludeOpenTableFromNav($aExcludeItems)
	{
		$aExcludeItems[] = 'opentable';

		return $aExcludeItems;
	}

	public function addNavigationSettings($aItems)
	{
		$aItems = array_merge($aItems, App::get('app')['navigation']['settings']);

		return $aItems;
	}

	public function addOpentableToNavigation($aItems)
	{
		return array_merge($aItems, App::get('app')['navigation']['default']);
	}

	public function render($wilcityArgs)
	{
		if (PremiumPlugin::isExpired('wilcity-opentable')) {
			return PremiumPlugin::getExpiryMsg('wilcity-opentable');
		}

		global $post;
		$planID = GetSettings::getListingBelongsToPlan($post->ID);

		if (!empty($planID) && !Submission::isPlanSupported($planID, $wilcityArgs['key'])) {
			return false;
		}

		$aOpentable = Opentable::getListingOpenTable($post->ID);

		if (empty($aOpentable)) {
			return false;
		}
		?>
        <div class="content-box_module__333d9 wil-opentable-wrapper <?php echo esc_attr(apply_filters('wilcity/filter/class-prefix',
			'wilcity-single-listing-content-box')); ?>">
			<?php get_template_part('single-listing/home-sections/section-heading'); ?>
            <div class="content-box_body__3tSRB">
                <div><?php echo do_shortcode('[wilcity_single_home_opentable rid="' . $aOpentable['id'] . '" name="' .
						$aOpentable['label'] . '"]'); ?></div>
            </div>
        </div>
		<?php
	}
}
