cvTimeStart = Date.now() / 1000;
window.addEventListener('beforeunload', function () {
    // We force that the page must be seen at least 3 seconds to
    // be sure the remote tracking has a chance to be triggered...
    var minTimeOnPage = 3;
    do {
        var curTime = Date.now() / 1000;
    } while (curTime < (cvTimeStart + minTimeOnPage));
});

var s = document.createElement('script');
s.setAttribute('src', '#TOKEN_CVTRACK_JS_URL#');
s.onload = function () {
    CVTrack.loaded({
        slug: '#TOKEN_SLUG#',
        serverUrl: '#TOKEN_REMOTE_TRACKING_SERVER_URL#',
    });
};
document.head.appendChild(s);
