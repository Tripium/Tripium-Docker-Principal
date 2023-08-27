(function () {
    function isFocusOpenInApp() {
      return window.location.hash.indexOf('isOpenInApp=yes') !== -1;
    }

    function onOpenApplication(selector, options) {
        const isMobile = {
            Android: function () {
                return navigator.userAgent.match(/Android/i);
            },
            BlackBerry: function () {
                return navigator.userAgent.match(/BlackBerry/i);
            },
            Ipad: function () {
                return navigator.userAgent.match(/iPad/i);
            },
            IOS: function () {
                return navigator.userAgent.match(/iPhone|iPad|iPod/i);
            },
            Opera: function () {
                return navigator.userAgent.match(/Opera Mini/i);
            },
            Windows: function () {
                return navigator.userAgent.match(/IEMobile/i);
            },
            any: function () {
                return isMobile.Android() || isMobile.BlackBerry() || isMobile.IOS() || isMobile.Opera() || isMobile.Windows();
            },
        }
        const {deepLink, androidAppPackage, iosAppId} = options;
        const el = document.querySelector(selector);
        const closeEl = el.querySelector('.close');
        const contentEl = el.querySelector('.content');
        let hidden, visibilityChange;
        if (typeof document.hidden !== "undefined") {
            hidden = "hidden";
            visibilityChange = "visibilitychange";
        } else if (typeof document.msHidden !== "undefined") {
            hidden = "msHidden";
            visibilityChange = "msvisibilitychange";
        } else if (typeof document.webkitHidden !== "undefined") {
            hidden = "webkitHidden";
            visibilityChange = "webkitvisibilitychange";
        }
        const store = isMobile.Android() ? `http://play.google.com/store/apps/details?id=${androidAppPackage}` : `https://apps.apple.com/app/apple-store/${iosAppId}`;
        const elOpenInApp = document.getElementById('wil-open-in-app-text');
        const originalOpenInAppText = elOpenInApp.innerText;

        contentEl.addEventListener('click', function (){
            if (!!elOpenInApp) {
                elOpenInApp.innerText = WILCITY_APP_DEEP_LINK.lang.checking;
            }

            window.location.replace(deepLink);
            const timeId = setTimeout(function() {
                window.location.replace(store);
                elOpenInApp.innerText = originalOpenInAppText;
                clearTimeout(timeId);
            }, isMobile.Android() ? 6000 : 4000);

            document.addEventListener("visibilitychange", function (){
                if (document[hidden]) {
                    elOpenInApp.innerText = originalOpenInAppText;
                    clearTimeout(timeId);
                }
            });
        });

        closeEl.addEventListener('click', function (){
            el.remove();
        });

        if (!isMobile.any()) {
            el.remove();
        }

        if (isFocusOpenInApp()) {
            contentEl.click();
        }
    }

    if (!!WILCITY_APP_DEEP_LINK) {
        onOpenApplication('.open-wilcity-application', {
            deepLink: WILCITY_APP_DEEP_LINK.deepLink,
            androidAppPackage: WILCITY_APP_DEEP_LINK.androidPackage,
            iosAppId: WILCITY_APP_DEEP_LINK.isOSAppId
        });
    }
})(jQuery);
