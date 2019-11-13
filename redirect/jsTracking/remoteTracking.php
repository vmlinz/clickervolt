<?php

namespace ClickerVolt;

header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../utils/beaconTools.php';
BeaconTools::beaconVarsToGET();

require_once __DIR__ . '/../session/sessionClick.php';
require_once __DIR__ . '/../../db/tableAIDs.php';
require_once __DIR__ . '/../../db/tableLinks.php';
require_once __DIR__ . '/../../db/tableSuspiciousClicks.php';
require_once __DIR__ . '/../../db/objects/maybeSuspiciousClick.php';
require_once __DIR__ . '/../../db/objects/cvSettings.php';
require_once __DIR__ . '/../../utils/cookieTools.php';
require_once __DIR__ . '/../../utils/dataProxy.php';
require_once __DIR__ . '/../../utils/urlTools.php';
require_once __DIR__ . '/../../utils/logger.php';


if (!empty($_GET) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
        case 'trackView':
            trackView();
            break;

        case 'trackTime':
            trackTime();
            break;

        case 'trackSuspiciousScore':
            trackSuspiciousScore();
            break;

        case 'trackIfHuman':
            trackIfHuman();
            break;

        case 'bt':
            flagVisitAsBot();
            break;
    }
}

/**
 * 
 */
function addInfoToGlobals()
{
    $from = null;
    if (!empty($_GET['from'])) {
        $from = $_GET['from'];
    } else if (!empty($_POST['from'])) {
        $from = $_POST['from'];
    }

    $_SERVER['HTTP_REFERER'] = null;
    if (!empty($_GET['ref'])) {
        $referrer = base64_decode($_GET['ref']);
        if (!empty($referrer)) {
            $_SERVER['HTTP_REFERER'] = $referrer;

            $referrerVars = URLTools::getParams($referrer, false);
            if (!empty($referrerVars)) {
                $_GET = array_merge($referrerVars, $_GET);
            }
        }
    }

    if ($from) {
        $fromVars = URLTools::getParams($from, true);
        if (!empty($fromVars)) {
            $_GET = array_merge($_GET, $fromVars);
        }
    }
}

/**
 * 
 */
function trackView()
{
    $response = [
        'trackView' => 1
    ];

    if (!empty($_GET['slug'])) {
        $slug = $_GET['slug'];

        addInfoToGlobals();

        $session = new SessionClick();
        $clickId = $session->getLatestClickId($slug);

        if ($clickId) {
            $clickInfo = $session->getClickInfo($clickId);
            if ($clickInfo && $clickInfo->getSlug() == $slug) {
                if ($clickInfo->isOrganic()) {
                    // That latest click was from an organic view.
                    // Let's reset it, so we can record a new organic view from this same visitor for that same slug.
                    $clickId = null;
                }
            }
        }

        if (!$clickId) {
            // This is an organic visit
            require_once __DIR__ . '/../router.php';

            $_GET[Router::QUERY_KEY_SLUG] = $slug;
            $_GET[Router::QUERY_KEY_SOURCE] = empty($_GET['src']) ? null : $_GET['src'];
            $_GET[Router::QUERY_KEY_URL] = empty($_GET['from']) ? null : urlencode($_GET['from']);

            $router = new Router(Router::TYPE_QUERY_PARAMS);
            $router->goToFinalURL(true);
        }

        $clickId = $session->getLatestClickId($slug);
        if ($clickId) {
            $clickInfo = $session->getClickInfo($clickId);
            if ($clickInfo) {
                $response['clickInfo'] = $clickInfo->toArray();
            }
        }

        $link = DataProxy::getLink($slug);
        if ($link) {
            $htmlContent = $link->getHTMLHooks(Link::HTML_HOOK_WHEN_AFTER);
            if (!empty($htmlContent)) {
                $response['htmlAfterRedirect'] = implode('<br>', $htmlContent);
            }
            $settings = $link->getSettings();
            if (isset($settings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS])) {
                $fraudOptions = $settings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS];

                switch ($fraudOptions[Link::SETTINGS_FRAUD_DETECTION_MODE]) {
                    case Link::FRAUD_DETECTION_MODE_RECAPTCHA_V3:
                        $response['fraudDetection'] = [
                            'recaptchaV3' => [
                                'siteKey' => $fraudOptions[Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_SITE_KEY],
                                'hideBadge' => $fraudOptions[Link::SETTINGS_FRAUD_DETECTION_RECAPTCHA_V3_HIDE_BADGE],
                            ]
                        ];
                        break;

                    case Link::FRAUD_DETECTION_MODE_HUMAN:
                        $response['fraudDetection'] = [
                            'human' => [
                                'a' => $clickId,
                                'b' => $slug,
                                'c' => $link->getId(),
                                'd' => session_id(),
                                'r1' => mt_rand(100, 10000),
                                'r2' => mt_rand(100, 10000),
                            ]
                        ];
                        break;
                }
            }
        }
    }

    echo json_encode($response);
}

/**
 * 
 */
function trackTime()
{
    if (!empty($_GET['timeOnPage'])) {
        $timeOnPage = $_GET['timeOnPage'];

        addInfoToGlobals();

        $clickId = null;
        $session = new SessionClick();

        if (!empty($_GET['slug'])) {
            $slug = $_GET['slug'];
            $clickId = $session->getLatestClickId($slug);

            if ($clickId) {
                $clickInfo = $session->getClickInfo($clickId);
                if ($clickInfo && $slug == $clickInfo->getSlug()) {
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
    }
}

/**
 * Track with Google Recaptcha V3
 */
function trackSuspiciousScore()
{
    require_once __DIR__ . '/../../utils/remote.php';
    require_once __DIR__ . '/../../utils/urlTools.php';

    $response = [
        'trackSuspiciousScore' => 1,
        'score' => 'unknown',
    ];

    if (!empty($_GET['token'])) {
        $token = $_GET['token'];

        addInfoToGlobals();

        $clickId = null;
        $session = new SessionClick();

        if (!empty($_GET['slug'])) {
            $slug = $_GET['slug'];
            $clickId = $session->getLatestClickId($slug);

            if ($clickId) {
                $clickInfo = $session->getClickInfo($clickId);
                if ($clickInfo && $slug == $clickInfo->getSlug()) {

                    $params = [
                        'token' => $token,
                    ];

                    $endpoint = URLTools::getRestURL('/clickervolt/api/v1/recaptchaVerify');
                    list($data, $lastURL) = Remote::singleton()->post($endpoint, $params);
                    $result = json_decode($data, true);
                    if (!$result || empty($result['body'])) {
                        throw new \Exception("Error in response: {$data}");
                    }

                    $result = json_decode($result['body'], true);
                    if (
                        $result
                        && !empty($result['success'])
                        && array_key_exists('score', $result)
                        && array_key_exists('action', $result)
                        && str_replace('_', '-', $result['action']) == $slug
                    ) {
                        $score = $result['score'];

                        // Max score I can get from my own legit visits is 0.9
                        // Let's rescale to make 0.9 the best quality (100)
                        // but ONLY if current score is > 0.5 (so we don't increase low-quality scores)
                        if ($score > 0.5) {
                            $score = min(0.9, $score) / 0.9;
                        }
                        $score = 100 - round(100 * $score);
                        $response['score'] = $score;

                        if ($score > 0 && !CookieTools::isLoggedIn()) {
                            $suspiciousClick = new SuspiciousClick($clickId, $score);
                            $suspiciousClick->queue();
                        }
                    }
                }
            }
        }
    }

    echo json_encode($response);
}

/**
 * 
 */
function trackIfHuman()
{
    addInfoToGlobals();

    $botResponse = function () {
        echo json_encode(['human' => 0]);
        die;
    };
    $humanResponse = function () {
        echo json_encode(['human' => 1]);
        die;
    };

    $requiredParams = [
        'slug', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'
    ];

    foreach ($requiredParams as $param) {
        if (!array_key_exists($param, $_POST)) {
            // Failed validation: not human
            return;
        }
    }

    $clickId = (string) $_POST['a'];
    $slug = (string) $_POST['b'];
    $linkId = (string) $_POST['c'];
    $sessionId = (string) $_POST['d'];
    $concatABCD = (string) $_POST['e'];
    $f = $_POST['f'];
    $g = $_POST['g'];
    $h = $_POST['h'];

    if ($concatABCD != $clickId . $slug . $linkId . $sessionId) {
        // Failed validation: not human
        $botResponse();
    }

    if ($h != ($f * $g)) {
        // Failed validation: not human
        $botResponse();
    }

    $session = new SessionClick();
    if ($sessionId != session_id()) {
        // Failed validation: not human
        $botResponse();
    }

    if ($session->getLatestClickId($slug) != $clickId) {
        // Failed validation: not human
        $botResponse();
    }

    $clickInfo = $session->getClickInfo($clickId);
    if (!$clickInfo || $slug != $clickInfo->getSlug()) {
        // Failed validation: not human
        $botResponse();
    }

    $link = DataProxy::getLink($slug);
    if (!$link) {
        // Failed validation: not human
        $botResponse();
    }

    if ($link->getId() != $linkId) {
        // Failed validation: not human
        $botResponse();
    }

    // Passed all tests... this is not a suspicious click

    $path = MaybeSuspiciousClick::getPath($clickId);
    if (file_exists($path)) {
        unlink($path);
    }

    $humanResponse();
}

/**
 * 
 */
function flagVisitAsBot()
{
    $session = new SessionClick();
    $clickInfo = $session->getClickInfo();
    if ($clickInfo) {
        $suspiciousClick = new SuspiciousClick($clickInfo->getClickId(), 100);
        $suspiciousClick->queue();

        //Logger::getGeneralLogger()->log("flagVisitAsBot(): " . json_encode($clickInfo->toArray()));
    }
}
