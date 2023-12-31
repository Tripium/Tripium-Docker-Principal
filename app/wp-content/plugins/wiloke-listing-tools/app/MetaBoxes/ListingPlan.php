<?php

namespace WilokeListingTools\MetaBoxes;

use WC_Subscriptions_Product;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\WooCommerce as WooCommerceHelpers;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Models\PlanRelationshipModel;

class ListingPlan extends Controller
{
    public function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [$this, 'addListingPricingTab']);
        add_action('woocommerce_product_data_panels', [$this, 'addListingPricingContent']);
        add_action('woocommerce_process_product_meta', [$this, 'saveListingPlan']);
        add_action('woocommerce_process_product_meta_simple', [$this, 'saveListingPlan']);
        add_action('woocommerce_process_product_meta_variable', [$this, 'saveListingPlan']);
        add_action('cmb2_admin_init', [$this, 'renderMetaboxFields']);
        add_action('add_meta_boxes', [$this, 'registerMetaBoxes'], 10, 5);
        add_action('wp_ajax_wilcity_set_plan_directly', [$this, 'setPlanDirectly']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter(
            'wiloke-listing-tools/config/listingplan/listing_plan_category',
            [$this, 'addCustomFieldToListingPlans']
        );
        add_action('save_post', [$this, 'updatedListingBelongsTo'], 10, 2);
        add_action('init', [$this, 'updatedListingBelongsToWP53'], 1);
    }

    /**
     * Auto-update Period day, Trial Period Day, Regular Price if Product Alias is not empited and it's Woocommerce
     * Subscription Product Alias
     *
     * @since 1.2.0
     */
    private function autoUpdatePeriodDayAndPriceIfHasWooCommerceSubscription(
        $listingPlanID,
        $productID,
        $isPlanPage = false
    )
    {
        if (WooCommerceHelpers::isSubscriptionProduct($productID)) {
            $regularPrice = get_post_meta($productID, '_subscription_price', true);
            $trialLength = WC_Subscriptions_Product::get_trial_length($productID);

            $regularInterval = WC_Subscriptions_Product::get_interval($productID);
            $regularPeriod = WC_Subscriptions_Product::get_period($productID);

            $aListingPlanSettings = GetSettings::getPlanSettings($listingPlanID);
            $aListingPlanSettings['regular_period']
                = WooCommerceHelpers::convertPeriodToDays($regularInterval, $regularPeriod);

            if (!empty($trialLength)) {
                $aListingPlanSettings['trial_period'] = WooCommerceHelpers::convertPeriodToDays($trialLength,
                    WC_Subscriptions_Product::get_trial_period($productID));
            }

            $aListingPlanSettings['regular_price'] = $regularPrice;
            if (!$isPlanPage) {
                SetSettings::setPlanSettings($listingPlanID, $aListingPlanSettings);
            }
        } else {
            $regularPrice = get_post_meta($productID, '_regular_price', true);
            $aListingPlanSettings = GetSettings::getPlanSettings($listingPlanID);
            $aListingPlanSettings['regular_price'] = $regularPrice;
            if (!$isPlanPage) {
                SetSettings::setPlanSettings($listingPlanID, $aListingPlanSettings);
            }
        }
        PlanRelationshipModel::deleteAllRelationshipBetweenProductAndPlan($productID);
        SetSettings::deletePostMeta($listingPlanID, 'woocommerce_association');
        SetSettings::setPostMeta($listingPlanID, 'woocommerce_association', $productID);
    }

    public function updatedListingBelongsToWP53()
    {
        if (!$this->isAdminEditing() || !$this->checkAdminReferrer() || !$this->isWP53() ||
            !$this->isPostType('listing_plan')) {
            return false;
        }

        $this->updatedListingBelongsTo($_GET['post'], get_post($_GET['post']));
    }

    public function updatedListingBelongsTo($listingPlanID, $post)
    {
        if (!current_user_can('administrator') || !is_admin() || empty($post) ||  $post->post_type != 'listing_plan') {
            return false;
        }

        $listingPlanID = absint($listingPlanID);

        if (isset($_POST['wilcity_woocommerce_association']) && !empty($_POST['wilcity_woocommerce_association'])) {
            $productID = absint($_POST['wilcity_woocommerce_association']);
            $this->autoUpdatePeriodDayAndPriceIfHasWooCommerceSubscription($listingPlanID, $productID, true);
            SetSettings::deletePostMeta($productID, 'listing_plan');
            SetSettings::setPostMeta($productID, 'listing_plan', $listingPlanID);
        }
    }

    public function saveListingPlan($productID)
    {
        if (!current_user_can('administrator') || !is_admin()) {
            return false;
        }

        if (isset($_POST['listing_plan']) && !empty($_POST['listing_plan'])) {
            $listingPlanID = absint($_POST['listing_plan']);
            SetSettings::deletePostMeta($productID, 'listing_plan');
            SetSettings::setPostMeta($productID, 'listing_plan', $listingPlanID);
            $this->autoUpdatePeriodDayAndPriceIfHasWooCommerceSubscription($listingPlanID, $productID);
        } else {
            SetSettings::deletePostMeta($productID, 'listing_plan');
            PlanRelationshipModel::deleteAllRelationshipBetweenProductAndPlan($productID);
        }
    }

    public static function getMaximumGalleryImagesAllowed()
    {
        if (!current_user_can('administrator')) {
            return 10;
        }
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return 10;
        }

        $aPlanSettings = GetSettings::getPlanSettings($_GET['post']);
        if (isset($aPlanSettings['maximumGalleryImages'])) {
            return absint($aPlanSettings['maximumGalleryImages']);
        }

        return 10;
    }

    public static function getMaximumVideosAllowed()
    {
        if (!current_user_can('administrator')) {
            return 4;
        }
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return 4;
        }

        $aPlanSettings = GetSettings::getPlanSettings($_GET['post']);
        if (isset($aPlanSettings['maximumVideos'])) {
            return absint($aPlanSettings['maximumVideos']);
        }

        return 4;
    }

    public function enqueueScripts($hook)
    {
        if (General::detectCurrentPostType() !== 'listing_plan') {
            return false;
        }

        wp_enqueue_script('listing-plan', WILOKE_LISTING_TOOL_URL . 'admin/source/js/listing-plan.js', ['jquery'],
            WILOKE_LISTING_TOOL_VERSION, true);
    }

    public function setPlanDirectly()
    {
        if (!current_user_can('administrator')) {
            wp_send_json_error(['msg' => 'ERROR: You do not have permission to access this page.']);
        }

        if (empty($_POST['postID'])) {
            wp_send_json_error(['msg' => 'ERROR: The Plan ID is emptied!']);
        }

        $aConfiguration = GetWilokeSubmission::getAll();

        if (isset($_POST['belongsTo']) && !empty($_POST['belongsTo'])) {
            $aPlans = GetWilokeSubmission::getAddListingPlans($_POST['belongsTo']);
            $key = array_search($_POST['postID'], $aPlans);
            unset($aPlans[$key]);
            $aConfiguration[$_POST['belongsTo']] = implode(',', $aPlans);
        }

        $newPlanKey = $_POST['changeBelongsTo'] . '_plans';
        $aChangeBelongsTo = GetWilokeSubmission::getAddListingPlans($newPlanKey);
        $aChangeBelongsTo[] = $_POST['postID'];
        $aConfiguration[$newPlanKey] = implode(',', $aChangeBelongsTo);

        update_option('wiloke_submission_configuration', maybe_serialize($aConfiguration));
        wp_send_json_success(['msg' => 'Congratulations! This plan has been added to ' . $_POST['changeBelongsTo']]);
    }

    public function renderBelongsToBox()
    {
        $aPostTypes = General::getPostTypes(false, false);
        $belongsTo = '';
        ?>
        <p>Need help? <a href="https://documentation.wilcity.com/knowledgebase/setting-up-package-setting/"
                         target="_blank">Setting up Plans to Each Listing Type</a></p>

        <p>
            <label for="wilcity-change-plan-belongs-to"><strong>Set this plan to</strong></label>
            <select name="belongs_to" id="wilcity-change-plan-belongs-to">
                <option value="">---</option>
                <?php
                foreach ($aPostTypes as $postType => $aPostType):
                    $aPlanIDs = GetWilokeSubmission::getAddListingPlans($postType . '_plans');
                    if (isset($_GET['post']) && !empty($_GET['post']) && !empty($aPlanIDs) &&
                        in_array($_GET['post'], $aPlanIDs)
                    ) :
                        $belongsTo = $postType . '_plans';
                        ?>
                        <option value="<?php echo esc_attr($postType); ?>"
                                selected><?php echo esc_html($aPostType['singular_name'] . ' Plan'); ?></option>
                    <?php else: ?>
                        <option
                            value="<?php echo esc_attr($postType); ?>"><?php echo esc_html($aPostType['singular_name'] .
                                ' Plan'); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="wilcity-plan-belongs-to" value="<?php echo esc_attr($belongsTo); ?>">
            <button id="wilcity-add-plan-to-directory-directly" class="button button-primary button-large">Execute
            </button>
        </p>
        <?php
    }

    public function registerMetaBoxes()
    {
        add_meta_box(
            'wilcity-listing-plan-belongs-to-addlisting',
            'Belongs To', [$this, 'renderBelongsToBox'],
            'listing_plan',
            'normal'
        );
    }

    public function renderMetaboxFields()
    {
        foreach (wilokeListingToolsRepository()->get('listingplan') as $aSettings) {
            $aSettings = apply_filters('wiloke-listing-tools/config/listingplan/' . $aSettings['id'], $aSettings);
            new_cmb2_box($aSettings);
        }
    }

    protected function determineBelongToDirectoryType($planID)
    {
        $aPostTypes = General::getPostTypeKeys(false, false);
        foreach ($aPostTypes as $postType) {
            $aPlanIDs = GetWilokeSubmission::getAddListingPlans($postType . '_plans');
            if (!empty($aPlanIDs) && in_array($planID, $aPlanIDs)) {
                return $postType;
            }
        }

        return false;
    }

    public function addCustomFieldToListingPlans($aSettings)
    {
        if (!isset($_GET['post']) || empty($_GET['post'])) {
            return $aSettings;
        }
        $planID = $_GET['post'];
        $belongsTo = $this->determineBelongToDirectoryType($planID); //postType

        if (!$belongsTo) {
            return $aSettings;
        }

        $aUsedSections = General::getCustomFieldsOfPostType($belongsTo);
        $aGroups = General::getCustomGroupsOfPostType($belongsTo);
        if (empty($aUsedSections) && empty($aGroups)) {
            return $aSettings;
        }

        $aCustomFields = [];

        if (!empty($aUsedSections)) {
            $aCustomFields = $aUsedSections;
        }

        if (!empty($aGroups)) {
            $aCustomFields = array_merge($aCustomFields, $aGroups);
        }

        foreach ($aCustomFields as $aFieldSettings) {
            $aSettings['fields'][] = [
                'type'      => 'wiloke_field',
                'fieldType' => 'select',
                'id'        => 'add_listing_plan:toggle_' . $aFieldSettings['key'],
                'name'      => 'Toggle ' . $aFieldSettings['heading'],
                'options'   => [
                    'enable'  => 'Enable',
                    'disable' => 'Disable'
                ]
            ];
        }

        return $aSettings;
    }

    public static function renderProductAlias()
    {
        $aPosts = get_posts(
            [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ]
        );

        $aOptions = [
            '' => '----'
        ];

        foreach ($aPosts as $post) {
            $aOptions[$post->ID] = $post->post_title;
        }

        return $aOptions;
    }

    public function addListingPricingTab($aProductDataTabs)
    {
        $aProductDataTabs['listing-pricing'] = [
            'label'  => esc_html__('Listing pricing', 'wiloke-listing-tools'),
            'target' => 'wilcity_listing_pricing',
        ];

        return $aProductDataTabs;
    }

    public function getPricing()
    {
        global $wpdb;
        $aResults
            = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='listing_plan' AND post_status='publish' LIMIT 100");

        if (empty($aResults) || is_wp_error($aResults)) {
            return false;
        }

        $aOptions = [
            '' => '----'
        ];
        foreach ($aResults as $oResult) {
            $aOptions[$oResult->ID] = $oResult->post_title;
        }

        return $aOptions;
    }

    public function addListingPricingContent()
    {
        global $post;
        $id = $post->ID;
        $aOptions = $this->getPricing();
        ?>
        <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
        <div id="wilcity_listing_pricing" class="panel woocommerce_options_panel">
            <?php
            if (empty($aOptions)) {
                ?>
                <strong><?php esc_html_e('There is no Listing pricing', 'wiloke-listing-tools'); ?></strong>
                <?php
            } else {
                $val = GetSettings::getPostMeta($id, 'listing_plan');
                woocommerce_form_field('listing_plan', [
                    'type'    => 'select',
                    'class'   => ['wps-drop'],
                    'label'   => esc_html__('Select a Listing Pricing', 'wiloke-listing-tools'),
                    'options' => $aOptions
                ], $val);
            }
            ?>
        </div>
        <?php
    }
}
