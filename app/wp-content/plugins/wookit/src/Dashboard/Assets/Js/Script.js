
jQuery(document).ready(function () {
    const iframe = document.getElementById("shopkit-iframe");

    function authen() {
        jQuery.ajax({
            data: {
                action: "wookit_getCodeAuth", //Tên action, dữ liệu gởi lên cho server
            },
            method: "POST",
            url: ajaxurl,
            success: function (response) {
                iframe.addEventListener("load", function () {
                    iframe.contentWindow.postMessage(
                        {
                            payload: {
                                url: window.WookitGLOBAL.restBase,
                                token: response.data.code,
                                tidioId: window.WookitGLOBAL.tidio || "",
                                clientSite: window.WookitGLOBAL.clientSite || "",
                                email: window.WookitGLOBAL.email || "",
                                purchaseCode: window.WookitGLOBAL.purchaseCode || "",
                                purchaseCodeLink: window.WookitGLOBAL.purchaseCodeLink || "",
                                endpointVerification:
                                    window.WookitGLOBAL.endpointVerification || "",
                            },
                            type: "@AjaxToken",
                        },
                        "*"
                    );
                    iframe.classList.remove("hidden");
                });
                if (iframe) {
                    iframe.contentWindow.postMessage(
                        {
                            payload: {
                                url: window.WookitGLOBAL.restBase,
                                token: response.data.code,
                                tidioId: window.WookitGLOBAL.tidio || "",
                                clientSite: window.WookitGLOBAL.clientSite || "",
                                email: window.WookitGLOBAL.email || "",
                                purchaseCode: window.WookitGLOBAL.purchaseCode || "",
                                purchaseCodeLink: window.WookitGLOBAL.purchaseCodeLink || "",
                                endpointVerification:
                                    window.WookitGLOBAL.endpointVerification || "",
                            },
                            type: "@AjaxToken",
                        },
                        "*"
                    );
                }
            },
            error: function (response) {
                iframe.addEventListener("load", function () {
                    iframe.contentWindow.postMessage(
                        {
                            payload: {
                                url: window.WookitGLOBAL.restBase,
                                token: "",
                                tidioId: window.WookitGLOBAL.tidio || "",
                                clientSite: window.WookitGLOBAL.clientSite || "",
                                email: window.WookitGLOBAL.email || "",
                                purchaseCode: window.WookitGLOBAL.purchaseCode || "",
                                purchaseCodeLink: window.WookitGLOBAL.purchaseCodeLink || "",
                                endpointVerification:
                                    window.WookitGLOBAL.endpointVerification || "",
                            },
                            type: "@AjaxToken",
                        },
                        "*"
                    );
                    iframe.classList.remove("hidden");
                });
            },
        });
    }

    authen();

    window.addEventListener(
        "message",
        (event) => {
            if (event.data.type === "@HasPassed") {
                if (event.data.payload.hasPassed === true) {
                    authen();
                }
            }
        },
        false
    );
    jQuery("#btn-Revoke-Purchase-Code").click(function () {
        let status = confirm("Are you sure you want to revoke the Purchase Code?");
        if (status) {
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wookit_revokePurchaseCode",
                    purchaseCode: WookitGLOBAL.purchaseCode,
                },
                success: function (response) {
                    location.reload();
                },
                error: function (jqXHR, error, errorThrown) {
                    alert(jqXHR.responseJSON.message);
                },
            });
        }
    });
});
