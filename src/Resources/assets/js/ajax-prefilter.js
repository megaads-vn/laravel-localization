document.addEventListener("DOMContentLoaded", function(){
    $.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
        try {
            var requestUrl = options.url;
            var originUrl = new URL(window.location.href);
            var isContainHostName = requestUrl && requestUrl.includes(originUrl.origin);
            //k phai domain khac
            var isContainHttp = requestUrl && requestUrl.includes("http");
            if (isContainHostName) {
                requestUrl = requestUrl.replace(originUrl.hostname, originUrl.hostname + "/" + localePrefix);
            } else if (!isContainHttp) {
                if (requestUrl.charAt(0) != "/") {
                    requestUrl = "/" + requestUrl;
                }
                if (typeof localePrefix != 'undefined' && localePrefix !== '') {
                    requestUrl = "/" + localePrefix + requestUrl;
                }
            }
            options.url = requestUrl;
        } catch (err) {
        }
    });
});