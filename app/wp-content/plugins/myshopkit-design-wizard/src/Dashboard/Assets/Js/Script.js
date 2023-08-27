(function () {
    "use strict";

    jQuery(document).ready(function ($) {
        $("#wilsmSubmit").on('click', function () {
            let purchaseCode = $('#wilsmPurchase').val();
            if (purchaseCode) {
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "mskdw_verifyPurchaseCode",
                        purchaseCode: purchaseCode
                    },
                    success: function (response) {
                        console.log(response)
                        if (response.status === 'success') {
                            let notion = confirm(WILSM_GLOBAL.notionSuccessVerifyPurchaseCode);
                            if (notion) {
                                window.location.href = WILSM_GLOBAL.redirectDashBoard;
                            }
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function (jqXHR, error, errorThrown) {
                        alert(jqXHR.responseJSON.message);
                    },
                });
            } else {
                alert(WILSM_GLOBAL.notionErrorVerifyPurchaseCode);
            }
        });
        if (wp.media) {
            let l10n = wp.media.view.l10n;
            let frame = wp.media.view.MediaFrame.Select.prototype;
            frame.browseRouter = function (routerView) {
                routerView.set({
                    upload: {
                        text: l10n.uploadFilesTitle,
                        priority: 20
                    },
                    browse: {
                        text: l10n.mediaLibraryTitle,
                        priority: 40
                    },
                    wil_smart_mockup: {
                        text: WILSM_GLOBAL.pluginName,
                        priority: 60
                    }
                });
            };
            wp.media.view.Modal.prototype.on("open", function (e) {
                let browse = $('#menu-item-browse');
                browse.click();
            });
            $(wp.media).on('click', '#menu-item-wil_smart_mockup', function (e) {
                if (e.target.innerText === WILSM_GLOBAL.pluginName) {
                    /**
                     * How does it looks like?
                     * window.url = "https://editor-design-wizard.myshopkit.app/signin?token=" +WILSM_GLOBAL.token;
                     * window.windowOpen = window.open(window.url, '_blank');
                     * @type {WindowProxy}
                     */
                    window.windowOpen = window.open(WILSM_GLOBAL.urlIframe);
                    $("#media-attachment-date-filters").val('0').change();
                    let browse = $('#menu-item-browse');
                    browse.click();

                    const timer = setInterval(function () {
                        if (window.windowOpen.closed) {
                            let eventAll = $("#media-attachment-date-filters");
                            eventAll.val('all').change();
                            eventAll.click();
                            clearInterval(timer);
                            window.windowOpen = undefined;
                        }
                    }, 100);

                    $('#documentation').on('wilsm_close_editor',function () {
                        let eventAll = $("#media-attachment-date-filters");
                        eventAll.val('all').change();
                        eventAll.click();
                    })
                }

            });
        }

        const $iframeEl = document.querySelector('#mskdw-iframe');
        const windowTarget = $iframeEl ? $iframeEl.contentWindow : window.windowOpen;
        const urlTarget = $iframeEl ? "*" : window.url;
        window.addEventListener("message", (event) => {
            const {payload, type} = event.data
            if (type && type.startsWith('PROMOOLAND_request')) {
                const [_, id] = type.split('/');
                const emitSuccessEventType = `PROMOOLAND_success/${id}`;
                let urlType = payload.url;
                switch (urlType) {
                    case '/me/projects':
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "mskdw_getProjects",
                                params: payload.params,
                            },
                            success: function (response) {
                                windowTarget.postMessage(
                                    {
                                        payload: response,
                                        type: emitSuccessEventType
                                    },
                                    urlTarget
                                );

                            },
                            error: function (jqXHR, error, errorThrown) {
                                alert(jqXHR.responseJSON.message);
                            },
                        });
                        break;
                    case '/tags':
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "mskdw_crateTags",
                                params: payload.data,
                            },
                            success: function (response) {
                                windowTarget.postMessage(
                                    {
                                        payload: response,
                                        type: emitSuccessEventType
                                    },
                                    urlTarget
                                );
                            },
                            error: function (jqXHR, error, errorThrown) {
                                alert(jqXHR.responseJSON.message);
                            },
                        });
                        break;
                    case '/search':
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "mskdw_searchProjects",
                                params: payload.params,
                            },
                            success: function (response) {
                                windowTarget.postMessage(
                                    {
                                        payload: response,
                                        type: emitSuccessEventType
                                    },
                                    urlTarget
                                );
                            },
                            error: function (jqXHR, error, errorThrown) {
                                alert(jqXHR.responseJSON.message);
                            },
                        });
                        break;
                    case '/me/trash':
                        if (payload.method === 'DELETE') {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_deleteTrashProjects",
                                    params: payload.params,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        } else {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_getTrashProjects",
                                    params: payload.params,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        break;
                    case '/me/projects/create':
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "mskdw_createProjects",
                                params: payload.data,
                            },
                            success: function (response) {
                                windowTarget.postMessage(
                                    {
                                        payload: response,
                                        type: emitSuccessEventType
                                    },
                                    urlTarget
                                );
                            },
                            error: function (jqXHR, error, errorThrown) {
                                alert(jqXHR.responseJSON.message);
                            },
                        });
                        break;
                    case '/me/images':
                        if (payload.method === 'POST') {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_createImages",
                                    params: payload.data,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                            break;
                        }
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "mskdw_getImages",
                                params: payload.params,
                            },
                            success: function (response) {
                                windowTarget.postMessage(
                                    {
                                        payload: response,
                                        type: emitSuccessEventType
                                    },
                                    urlTarget
                                );
                            },
                            error: function (jqXHR, error, errorThrown) {
                                alert(jqXHR.responseJSON.message);
                            },
                        });
                        break;
                    default:
                        if (/\/me\/projects.[\d]+\/children/.test(urlType)) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_getChildrenProjects",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/trash/.test(urlType)) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_updateProjectToTrash",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/detail/.test(urlType) && (payload.method === 'PUT')) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_updateProject",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                    $("#media-attachment-date-filters").val('0').change();
                                    let browse = $('#menu-item-browse');
                                    browse.click();
                                    setTimeout(function () {
                                        window.close();
                                    }, 1000);
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/detail/.test(urlType) && (payload.method === 'DELETE')) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_deleteProject",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/detail\/publish/.test(urlType) && (payload.method === 'PATCH')) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_updateRestoreProject",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/detail\/trash/.test(urlType) && (payload.method === 'PATCH')) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_updateChildrenProjectToTrash",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/me\/projects.[\d]+\/create/.test(urlType) && (payload.method === 'POST')) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_createChildrenProject",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );

                                    let eventAll = $("#media-attachment-date-filters");
                                    eventAll.val('all').change();
                                    eventAll.click();
                                    setTimeout(function () {
                                        window.close();
                                    }, 1000);
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/me\/projects.[\d]+\/detail/.test(urlType)) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_getProjectDetail",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/images.[\d]+\/download/.test(urlType)) {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_downloadImage",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                        }
                        if (/\/images.[\d]+/.test(urlType) && payload.method === 'DELETE') {
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "mskdw_deleteImage",
                                    params: payload,
                                },
                                success: function (response) {
                                    windowTarget.postMessage(
                                        {
                                            payload: response,
                                            type: emitSuccessEventType
                                        },
                                        urlTarget
                                    );
                                },
                                error: function (jqXHR, error, errorThrown) {
                                    alert(jqXHR.responseJSON.message);
                                },
                            });
                            break;
                        }
                        break;
                }
            }


        }, false);

        window.onSaveSuccess = () => {
            window.close();
        }
    });

})(jQuery);
