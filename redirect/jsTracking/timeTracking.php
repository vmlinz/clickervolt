<?php

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// OBSOLETE - KEPT FOR BACKWARD COMPATIBILITY.
// NEW SCRIPT LOADS 'remoteTracking.php'
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////


namespace ClickerVolt;

require_once __DIR__ . '/../../utils/beaconTools.php';
BeaconTools::beaconVarsToGET();

require_once __DIR__ . '/../session/sessionClick.php';
require_once __DIR__ . '/../../db/tableAIDs.php';
require_once __DIR__ . '/../../db/tableLinks.php';
require_once __DIR__ . '/../../utils/cookieTools.php';
require_once __DIR__ . '/../../utils/dataProxy.php';

if (!empty($_GET) && !empty($_GET['timeOnPage'])) {

    $timeOnPage = $_GET['timeOnPage'];

    $clickId = null;
    $session = new SessionClick();

    if (!empty($_GET['slug'])) {
        $slug = $_GET['slug'];
        $clickId = $session->getLatestClickId($slug);

        if (!$clickId) {
            // This is an organic visit

            require_once __DIR__ . '/../router.php';
            $router = new Router(Router::TYPE_QUERY_PARAMS);

            if (!empty($_GET['from'])) {
                $fromVars = URLTools::getParams($_GET['from']);
                if (!empty($fromVars)) {
                    $_GET = array_merge($_GET, $fromVars);
                }
            }

            $_SERVER['HTTP_REFERER'] = null;
            if (!empty($_GET['ref'])) {
                $referrer = base64_decode($_GET['ref']);
                if (!empty($referrer)) {
                    $_SERVER['HTTP_REFERER'] = $referrer;
                }
            }

            $_GET[Router::QUERY_KEY_SLUG] = $slug;
            $_GET[Router::QUERY_KEY_SOURCE] = empty($_GET['src']) ? null : $_GET['src'];
            $_GET[Router::QUERY_KEY_URL] = empty($_GET['from']) ? null : urlencode($_GET['from']);
            $router->goToFinalURL(true);

            $clickId = $session->getLatestClickId($slug);
        }
    }

    if ($clickId) {
        $clickInfo = $session->getClickInfo($clickId);
        if ($clickInfo) {

            $slug = $clickInfo->getSlug();
            $link = DataProxy::getLink($slug);
            if ($link) {

                $aida = $link->getSettings()[Link::SETTINGS_AIDA];
                $attentionTrigger = $aida[Link::SETTINGS_AIDA_ATTENTION];
                $interestTrigger = $aida[Link::SETTINGS_AIDA_INTEREST];
                $desireTrigger = $aida[Link::SETTINGS_AIDA_DESIRE];

                if ($timeOnPage >= $attentionTrigger) {

                    $aid = [
                        'clickId' => $clickInfo->getClickId(),
                        'hasAttention' => 1
                    ];

                    if ($timeOnPage >= $interestTrigger) {
                        $aid['hasInterest'] = 1;

                        if ($timeOnPage >= $desireTrigger) {
                            $aid['hasDesire'] = 1;
                        }
                    }

                    if (!CookieTools::isLoggedIn()) {
                        $aid = new AID($aid);
                        $aid->queue();
                    }
                }
            }
        }
    }
}
