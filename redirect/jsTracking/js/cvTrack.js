
class CVTrack {

    static getClickInfo() {
        if (window.clickerVolt && window.clickerVolt.clickInfo) {
            return window.clickerVolt.clickInfo;
        }
        return null;
    }

    static getSuspiciousScore(callback) {
        if (window.clickerVolt && window.clickerVolt.suspiciousScore !== undefined) {
            callback(window.clickerVolt.suspiciousScore);
            return;
        }
        setTimeout(function () {
            CVTrack.getSuspiciousScore(callback);
        }, 500);
    }

    static loaded(options) {
        if (!window.clickerVolt) {
            window.clickerVolt = {
                timeOnPageStart: Date.now() / 1000,
                referrer: CVTrack.getReferrer(),
            };
        }

        if (options['slug'] && options['serverUrl']) {
            CVTrack.trackView(options['slug'], options['serverUrl']);
            CVTrack.trackTimeOnPage(options['slug'], options['serverUrl']);
        }
    }

    static trackView(slug, serverUrl) {
        var data = "action=trackView";
        data += "&slug=" + slug;
        data += "&from=" + encodeURIComponent(CVTrack.getHostURL());
        data += "&ref=" + btoa(window.clickerVolt.referrer);

        CVTrack.sendToServer(serverUrl, data, function (response) {
            try {
                var obj = JSON.parse(response);
                window.clickerVolt.clickInfo = obj.clickInfo;

                if (obj.htmlAfterRedirect) {
                    // The link contains some after-redirect HTML/JS hooks...
                    // Let's process them now!
                    CVTrack.injectHTML(obj.htmlAfterRedirect);
                }

                if (obj['fraudDetection']) {
                    var fraudDetectionSettings = obj['fraudDetection'];
                    if (fraudDetectionSettings['recaptchaV3']) {
                        CVTrack.trackSuspiciousScore(slug, serverUrl, fraudDetectionSettings['recaptchaV3']);
                    } else if (fraudDetectionSettings['human']) {
                        CVTrack.trackIfHuman(slug, serverUrl, fraudDetectionSettings['human']);
                    }
                }
            } catch (error) {
                console.log("CVTrack.trackView() error: " + error);
            }
        });
    }

    static trackTimeOnPage(slug, serverUrl) {
        window.addEventListener("beforeunload", function (event) {
            var data = "action=trackTime";
            data += "&timeOnPage=" + ((Date.now() / 1000) - window.clickerVolt.timeOnPageStart);
            data += "&slug=" + slug;
            data += "&from=" + encodeURIComponent(CVTrack.getHostURL());

            // this data not needed anymore, but kept for compatibility
            // with scripts loading old timeTracking.php
            data += "&ref=" + btoa(window.clickerVolt.referrer);

            CVTrack.sendToServer(serverUrl, data);
        });
    }

    static trackIfHuman(slug, serverUrl, humanSettings) {
        var data = "&slug=" + slug;
        data += "&from=" + encodeURIComponent(CVTrack.getHostURL());
        data += "&a=" + humanSettings['a'];
        data += "&b=" + humanSettings['b'];
        data += "&c=" + humanSettings['c'];
        data += "&d=" + humanSettings['d'];
        data += "&e=" + humanSettings['a'] + humanSettings['b'] + humanSettings['c'] + humanSettings['d'];
        var f = Math.ceil(Math.random() * humanSettings['r1']);
        var g = Math.ceil(Math.random() * humanSettings['r2']);
        data += "&f=" + f;
        data += "&g=" + g;
        data += "&h=" + (f * g);
        CVTrack.xhr("POST", serverUrl + '?action=trackIfHuman', data);
    }

    static trackSuspiciousScore(slug, serverUrl, recaptchaSettings) {
        var siteKey = recaptchaSettings['siteKey'];
        if (siteKey) {
            var hideBadge = recaptchaSettings['hideBadge'];

            if (hideBadge == 'yes') {
                // CSS to hide the recaptcha badge
                var css = document.createElement("style");
                css.type = "text/css";
                css.innerHTML = "div.grecaptcha-badge { display: none !important; }";
                document.body.appendChild(css);
            }

            var s = document.createElement('script');
            s.setAttribute('src', `https://www.google.com/recaptcha/api.js?render=${siteKey}`);
            s.onload = function () {
                grecaptcha.ready(function () {
                    var actionName = slug.split('-').join('_');
                    try {
                        grecaptcha.execute(siteKey, { action: actionName }).then(function (token) {
                            var data = "action=trackSuspiciousScore";
                            data += "&token=" + token;
                            data += "&slug=" + slug;
                            data += "&from=" + encodeURIComponent(CVTrack.getHostURL());
                            CVTrack.sendToServer(serverUrl, data, function (response) {
                                try {
                                    var obj = JSON.parse(response);
                                    window.clickerVolt.suspiciousScore = obj.score;
                                } catch (error) {
                                    console.log("CVTrack.trackSuspiciousScore() error: " + error);
                                }
                            });
                        });
                    } catch (error) {
                        console.log(error);
                    }
                });
            };
            document.head.appendChild(s);
        }
    }

    /**
     * 
     * @param {string} url 
     * @param {string} query - like 'var1=abc&var2=def'
     */
    static sendToServer(url, query, callback) {
        var useXHR = true;

        if (!callback && navigator.sendBeacon) {
            useXHR = !navigator.sendBeacon(url, query);
        }

        if (useXHR) {
            CVTrack.xhr("GET", url, query, callback);
        }
    }

    static xhr(method, url, params, callback) {
        var async = callback !== undefined;
        var request = new XMLHttpRequest();

        if (method == 'GET') {
            url += "?" + params;
            params = null;
        }
        request.open(method, url, async);

        if (method == 'POST') {
            request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }

        if (callback) {
            request.onreadystatechange = function () {
                // We don't use XMLHttpRequest.DONE anymore because
                // some libs override the XMLHttpRequest object.
                var DONE = 4;
                if (this.readyState === DONE && this.status === 200) {
                    callback(this.response);
                }
            }
        }
        request.send(params);
    }

    static getReferrer() {
        var ref = null;

        try {
            if (window.top !== window.self) {
                ref = window.top.document.referrer;
            } else {
                ref = document.referrer;
            }
        } catch (error) {
            ref = document.referrer;
        }

        if (!ref) {
            ref = '';
        }
        return ref;
    }

    static extractURLVars(url) {
        var vars = {};
        var searchIndex = url.indexOf("?");
        if (searchIndex !== -1) {
            var searchQuery = url.substring(searchIndex);
            for (var a = searchQuery.split("?"), t = a[1].split("&"), l = 0; l < t.length; l++) {
                var f = t[l].split("=");
                var key = f[0];
                var value = f[1];
                if (key !== undefined && value !== undefined) {
                    vars[key] = value;
                }
            }
        }

        return vars;
    }

    static getHostURL() {
        var url = document.URL;
        if (window.top !== window.self) {
            try {
                // We are re in an iframe, lets merge the query params from the top url with the ones from the iframe url
                var topParams = CVTrack.extractURLVars(window.top.document.URL);
                var iframeParams = CVTrack.extractURLVars(location.search);
                for (var k in topParams) {
                    iframeParams[k] = topParams[k];
                }

                var queryString = Object.keys(iframeParams).map(function (key) {
                    return key + "=" + iframeParams[key];
                }).join("&");

                if (url.indexOf("?") > 0) {
                    url = url.substring(0, url.indexOf("?"));
                }

                url += "?" + queryString;
            } catch (error) {
            }
        }

        return url;
    }

    static injectHTML(html, doc) {
        if (!doc) {
            doc = document;
        }
        var elements = CVTrack.htmlToElements(html, doc);
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            if (element.nodeName && element.nodeName.toUpperCase() == 'SCRIPT') {
                if (element.src) {
                    var tag = doc.createElement("script");
                    tag.src = element.src;
                    doc.head.appendChild(tag);
                } else {
                    eval(element.innerHTML, doc);
                }
            } else {
                doc.body.appendChild(element);
            }
        }
    }

    /**
     * @param {String} HTML representing any number of sibling elements
     * @return {NodeList} 
     */
    static htmlToElements(html, doc) {
        if (!doc) {
            doc = document;
        }
        var template = doc.createElement('template');
        template.innerHTML = html;
        var elements = [];
        for (var i = 0; i < template.content.childNodes.length; i++) {
            elements.push(template.content.childNodes[i]);
        }
        return elements;
    }
}
