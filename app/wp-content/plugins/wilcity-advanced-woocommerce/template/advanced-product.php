<?php
global $post, $wilcityArgs;
if (!function_exists('is_woocommerce')) {
    return false;
}

$checkoutType = WilokeThemeOptions::getOptionDetail('advanced_woo_checkout_type', 'redirect');
$checkoutTarget = WilokeThemeOptions::getOptionDetail('advanced_redirect_checkout_target', '_blank');
?>
<wil-lazy-load-component id="wil-product-<?php echo esc_attr($wilcityArgs['variant']); ?>" height="1px;">
    <template v-slot:default="{isInView}">
        <wil-single-list-products
          :post-id="<?php echo abs($post->ID); ?>"
          :settings='<?php echo json_encode($wilcityArgs); ?>'
          total-label="<?php echo esc_html__('Total', 'wilcity'); ?>"
          view-cart-label="<?php echo esc_html__('View Cart', 'wilcity'); ?>"
          checkout-label="<?php echo esc_html__('Checkout', 'wilcity'); ?>"
          cart-url="<?php echo esc_url(wc_get_cart_url()); ?>"
          checkout-type="<?php echo esc_attr($checkoutType); ?>"
          variant="<?php echo esc_attr($wilcityArgs['variant']); ?>"
          checkout-url="<?php echo esc_url(wc_get_checkout_url()); ?>"
          checkout-target="<?php echo esc_attr($checkoutTarget); ?>"
        >
        </wil-single-list-products>
    </template>
</wil-lazy-load-component>
