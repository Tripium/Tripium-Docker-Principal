(function ($){
    jQuery('body').on('switch-tab', function (event, tab) {
        if ($('.wil-instagram-shopify').length) {
            jQuery(window).trigger('resize');
            wilokeInstaFeedMounted();
        }
    });
})(jQuery);
