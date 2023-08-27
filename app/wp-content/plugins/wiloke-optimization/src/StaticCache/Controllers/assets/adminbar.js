(function () {
    let xhr = null;

    if (!jQuery('#wpadminbar').length) {
        setTimeout(function () {
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wiloke_optimization_render_admin_bar',
                    post_id: !!WILOKE_INLINE_GLOBAL.postID ? WILOKE_INLINE_GLOBAL.postID : 0,
                    term_id: !!WILOKE_INLINE_GLOBAL.termID ? WILOKE_INLINE_GLOBAL.termID : 0,
                },
                success: function (response) {
                    if (response.success) {
                        jQuery('body').append(response.data.html);
                        controlClearCacheBtns();
                    }
                }
            });
        }, 1000);
    }

    function handlePurgeCache(event, action) {
        event.preventDefault();
        const $btn = jQuery(event.target);
        const originalName = $btn.html();
        $btn.html('Processing');

        if (xhr !== null) {
            xhr.abort();
        }

        xhr = jQuery.ajax({
            type: 'POST',
            url: WILOKE_GLOBAL.ajaxurl,
            data: {
                action: 'wiloke_optimization_purge_cache',
                target: action,
                post_id: WILOKE_INLINE_GLOBAL.postID
            },
            success: function (response) {
                if (response.success) {
                    $btn.html('Success');
                    setTimeout(function () {
                        $btn.html(originalName);
                    }, 1000);
                } else {
                    alert(response.data.msg);
                }
            }
        })
    }

    function controlClearCacheBtns() {
        jQuery('#wp-admin-bar-wiloke_optimization_purge_all_cache').on('click', function (event) {
            handlePurgeCache(event, 'all_cache');
        });

        jQuery('#wp-admin-bar-wiloke_optimization_purge_static_pages').on('click', function (event) {
            handlePurgeCache(event, 'all_static_pages');
        });

        jQuery('#wp-admin-bar-wiloke_optimization_purge_static_page').on('click', function (event) {
            handlePurgeCache(event, 'current_page');
        });

        jQuery('#wp-admin-bar-wiloke_optimization_purge_nginx_cache').on('click', function (event) {
            handlePurgeCache(event, 'nginx');
        });

        jQuery('#wp-admin-bar-wiloke_optimization_purge_cloudflare_cache').on('click', function (event) {
            handlePurgeCache(event, 'cloudflare');
        });
    }

    controlClearCacheBtns();
})(jQuery);
