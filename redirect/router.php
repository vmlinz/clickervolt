<?php

namespace ClickerVolt;

if (!defined('DOING_AJAX') && !defined('DOING_CRON') && (!function_exists('is_admin') || !is_admin())) {
    require_once __DIR__ . '/../utils/beaconTools.php';
    BeaconTools::beaconVarsToGET();
}

require_once __DIR__ . '/../utils/dataProxy.php';
require_once __DIR__ . '/../utils/ipTools.php';
require_once __DIR__ . '/../utils/cookieTools.php';
require_once __DIR__ . '/../utils/uuid.php';
require_once __DIR__ . '/../db/tableLinks.php';
require_once __DIR__ . '/../db/tableClicks.php';
require_once __DIR__ . '/../db/tableURLsPaths.php';
require_once __DIR__ . '/../db/tableParallelIds.php';
require_once __DIR__ . '/../db/objects/suspiciousClick.php';
require_once __DIR__ . '/../db/objects/maybeSuspiciousClick.php';
require_once __DIR__ . '/session/sessionSlug.php';
require_once __DIR__ . '/session/sessionClick.php';
require_once __DIR__ . '/session/sessionFunnelLinks.php';
require_once __DIR__ . '/distributions/distributionRandom.php';
require_once __DIR__ . '/distributions/distributionSequential.php';
require_once __DIR__ . '/distributions/distributionAutoOptim.php';
require_once __DIR__ . '/dynamicTokens.php';

class Router
{

    const TYPE_VANITY_URL = 'vanity';
    const TYPE_QUERY_PARAMS = 'query';

    function __construct($type = self::TYPE_VANITY_URL)
    {
        $this->type = $type;
        date_default_timezone_set('UTC');
    }

    /**
     * 
     * @param bool $isOrganicView
     */
    function goToFinalURL($isOrganicView = false)
    {
        // If we're recording an organic view, then there's no redirect needed
        $blockRedirect = $isOrganicView;

        list($slug, $source) = $this->getCurrentSlugAndSource();
        if ($slug) {

            $link = DataProxy::getLink($slug);
            if ($link) {

                // Force reading of slug from loaded link, in case the passed slug was an alias
                $slug = $link->getSlug();
                list($urls, $weights) = $this->getUrlsAndWeights($link);

                $linkSettings = $link->getSettings();
                $distributionType = $linkSettings[Link::SETTING_DISTRIBUTION_TYPE];

                $options = [];
                if ($distributionType == DistributionRandom::TYPE) {
                    $options = [
                        DistributionRandom::OPTION_WEIGHTS => $weights
                    ];
                }

                $distribution = Distribution::getDistribution($distributionType);
                $url = $distribution->getFinalURL($urls, $slug, $options);

                $sessionClick = new SessionClick();
                $sourceClickInfo = null;

                $sessionFunnelLinks = new SessionFunnelLinks();
                list($parentClickId, $parentLinkId, $parentURL) = $sessionFunnelLinks->getParentInfo($link->getId());
                if ($parentClickId) {
                    $sourceClickInfo = $sessionClick->getClickInfo($parentClickId);
                } else if ($source == '') {
                    // Source not available in URL...
                    // Is it available in session for this same slug?

                    $clickInfo = $sessionClick->getClickInfo();
                    if ($clickInfo && $clickInfo->getSlug() == $slug) {
                        $sourceClickInfo = $clickInfo;
                    }
                }

                if ($sourceClickInfo) {
                    // Force source and Vx tokens to be the same ones as the source click
                    $source = $sourceClickInfo->getSource();
                    $_GET[self::QUERY_KEY_VAR_1] = $sourceClickInfo->getV1();
                    $_GET[self::QUERY_KEY_VAR_2] = $sourceClickInfo->getV2();
                    $_GET[self::QUERY_KEY_VAR_3] = $sourceClickInfo->getV3();
                    $_GET[self::QUERY_KEY_VAR_4] = $sourceClickInfo->getV4();
                    $_GET[self::QUERY_KEY_VAR_5] = $sourceClickInfo->getV5();
                    $_GET[self::QUERY_KEY_VAR_6] = $sourceClickInfo->getV6();
                    $_GET[self::QUERY_KEY_VAR_7] = $sourceClickInfo->getV7();
                    $_GET[self::QUERY_KEY_VAR_8] = $sourceClickInfo->getV8();
                    $_GET[self::QUERY_KEY_VAR_9] = $sourceClickInfo->getV9();
                    $_GET[self::QUERY_KEY_VAR_10] = $sourceClickInfo->getV10();
                    $_GET[self::QUERY_KEY_EXTERNAL_ID] = $sourceClickInfo->getExternalId();
                }

                $newClickId = UUID::alphaNum();
                $sessionFunnelLinks->setFunnelLinks($link->getId(), $link->getFunnelLinks(true), $newClickId, $url);
                list($linkIdsPath, $urlsPath) = $sessionFunnelLinks->getPathsTo($link->getId(), $url);

                $sessionSlug = new SessionSlug();

                $clickData = [
                    'id' => $newClickId,
                    'linkId' => $link->getId(),
                    'linkIdsPath' => implode(URLsPath::SEPARATOR, $linkIdsPath),
                    'urlsPath' => $urlsPath,
                    'source' => $source,
                    'url' => $url,
                    'timestamp' => time(),
                    'ip' => IPTools::getUserIP(),
                    'userAgent' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                    'language' => empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? null : $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                    'referrer' => empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER'],
                    'isUnique' => !$sessionSlug->hasVisitedSlugURL($slug, $url),
                    'v1' => array_key_exists(self::QUERY_KEY_VAR_1, $_GET) ? $_GET[self::QUERY_KEY_VAR_1] : null,
                    'v2' => array_key_exists(self::QUERY_KEY_VAR_2, $_GET) ? $_GET[self::QUERY_KEY_VAR_2] : null,
                    'v3' => array_key_exists(self::QUERY_KEY_VAR_3, $_GET) ? $_GET[self::QUERY_KEY_VAR_3] : null,
                    'v4' => array_key_exists(self::QUERY_KEY_VAR_4, $_GET) ? $_GET[self::QUERY_KEY_VAR_4] : null,
                    'v5' => array_key_exists(self::QUERY_KEY_VAR_5, $_GET) ? $_GET[self::QUERY_KEY_VAR_5] : null,
                    'v6' => array_key_exists(self::QUERY_KEY_VAR_6, $_GET) ? $_GET[self::QUERY_KEY_VAR_6] : null,
                    'v7' => array_key_exists(self::QUERY_KEY_VAR_7, $_GET) ? $_GET[self::QUERY_KEY_VAR_7] : null,
                    'v8' => array_key_exists(self::QUERY_KEY_VAR_8, $_GET) ? $_GET[self::QUERY_KEY_VAR_8] : null,
                    'v9' => array_key_exists(self::QUERY_KEY_VAR_9, $_GET) ? $_GET[self::QUERY_KEY_VAR_9] : null,
                    'v10' => array_key_exists(self::QUERY_KEY_VAR_10, $_GET) ? $_GET[self::QUERY_KEY_VAR_10] : null,
                    'externalId' => array_key_exists(self::QUERY_KEY_EXTERNAL_ID, $_GET) ? $_GET[self::QUERY_KEY_EXTERNAL_ID] : null,
                    'organic' => $isOrganicView,
                ];
                $click = new Click($clickData);
                if (!CookieTools::isLoggedIn()) {
                    if (!isset($linkSettings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS])) {
                        $linkSettings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS] = [
                            Link::SETTINGS_FRAUD_DETECTION_MODE => Link::FRAUD_DETECTION_MODE_NONE
                        ];
                    }
                    if ($linkSettings[Link::SETTINGS_FRAUD_DETECTION_OPTIONS][Link::SETTINGS_FRAUD_DETECTION_MODE] == Link::FRAUD_DETECTION_MODE_HUMAN) {
                        $maybeSuspiciousClick = new MaybeSuspiciousClick($newClickId);
                        $maybeSuspiciousClick->queue();
                    }

                    // Is it some parallel tracking request?
                    $pidParam = ParallelId::fromURL();
                    if ($pidParam) {
                        $pid = new ParallelId([
                            'parallelId' => $pidParam->getValue(),
                            'clickId' => $newClickId,
                            'clickData' => $clickData
                        ], true);
                        $pid->queue();
                    } else {
                        $click->queue();
                    }
                }

                $sessionSlug->addVisitedSlugURL($slug, $url);
                $sessionClick->setClickInfo(
                    new SessionClickInfo([
                        'clickId' => $click->getClickId(),
                        'timestamp' => $clickData['timestamp'],
                        'slug' => $slug,
                        'source' => $source,
                        'v1' => $clickData['v1'],
                        'v2' => $clickData['v2'],
                        'v3' => $clickData['v3'],
                        'v4' => $clickData['v4'],
                        'v5' => $clickData['v5'],
                        'v6' => $clickData['v6'],
                        'v7' => $clickData['v7'],
                        'v8' => $clickData['v8'],
                        'v9' => $clickData['v9'],
                        'v10' => $clickData['v10'],
                        'externalId' => $clickData['externalId'],
                        'organic' => $isOrganicView,
                    ])
                );

                if (isset($linkSettings[Link::SETTINGS_HOOKS][Link::SETTINGS_HOOKS_REDIRECTS])) {

                    require_once __DIR__ . '/hooks/redirectHooks.php';
                    require_once __DIR__ . '/../utils/urlTools.php';

                    $redirectHooks = $linkSettings[Link::SETTINGS_HOOKS][Link::SETTINGS_HOOKS_REDIRECTS];

                    if (isset($redirectHooks[Link::SETTINGS_HOOKS_REDIRECTS_PHP])) {
                        foreach ($redirectHooks[Link::SETTINGS_HOOKS_REDIRECTS_PHP] as $phpHook) {
                            RedirectHooks::executePHP(base64_decode($phpHook));
                        }
                    }

                    if (!$blockRedirect) {
                        if (isset($redirectHooks[Link::SETTINGS_HOOKS_REDIRECTS_HTML])) {
                            if ($linkSettings[Link::SETTINGS_REDIRECT_MODE] != Link::REDIRECT_MODE_CLOAKING && $linkSettings[Link::SETTINGS_REDIRECT_MODE] != Link::REDIRECT_MODE_VOLTIFY) {
                                $htmlContent = $link->getHTMLHooks(Link::HTML_HOOK_WHEN_BEFORE);
                                if (!empty($htmlContent)) {
                                    $maxDuration = 0;
                                    foreach ($redirectHooks[Link::SETTINGS_HOOKS_REDIRECTS_HTML] as $htmlHook) {
                                        $when = empty($htmlHook[Link::SETTINGS_HOOKS_REDIRECTS_HTML_WHEN])
                                            ? Link::HTML_HOOK_WHEN_BEFORE
                                            : $htmlHook[Link::SETTINGS_HOOKS_REDIRECTS_HTML_WHEN];

                                        if ($when == Link::HTML_HOOK_WHEN_BEFORE) {
                                            $maxDuration = max($maxDuration, $htmlHook[Link::SETTINGS_HOOKS_REDIRECTS_HTML_DURATION]);
                                        }
                                    }

                                    $nextURL = URLTools::getPluginURL();
                                    $queryVars = [
                                        'url=' . bin2hex($url),
                                        'mode=' . $linkSettings[Link::SETTINGS_REDIRECT_MODE],
                                        'slug=' . $slug,
                                    ];
                                    $nextURL .= '/redirect/redirectors/go.php?' . implode('&', $queryVars);

                                    $htmlContent = implode('<br>', $htmlContent);
                                    RedirectHooks::executeHTML($htmlContent, $maxDuration, $nextURL);
                                    exit;
                                }
                            }
                        }
                    }
                }

                if (!$blockRedirect) {
                    $_GET['url'] = bin2hex($url);
                    $_GET['mode'] = $linkSettings[Link::SETTINGS_REDIRECT_MODE];
                    $_GET['slug'] = $slug;
                    include __DIR__ . '/redirectors/go.php';
                }
            }
        }
    }

    /**
     * 
     * A slug can be suffixed with a source in this way: slug.source
     * 
     * @return array [ $slug, $source ] - null if no slug can be found in current url. Source canNOT be null, but can be empty ''
     * 
     */
    function getCurrentSlugAndSource()
    {
        $response = null;

        switch ($this->type) {
            case self::TYPE_VANITY_URL: {
                    if (!empty($_SERVER['REQUEST_URI'])) {

                        $urlParts = parse_url($_SERVER['REQUEST_URI']);
                        $pathParts = explode('/', $urlParts['path']);
                        $slug = explode('&', $pathParts[count($pathParts) - 1])[0];

                        $slugParts = explode('.', $slug);
                        if (count($slugParts) >= 2) {
                            $slug = $slugParts[0];
                            $source = $slugParts[1];
                        } else {
                            $source = '';
                        }

                        $response = [$slug, $source];
                    }
                }
                break;

            case self::TYPE_QUERY_PARAMS: {
                    $slug = empty($_GET[self::QUERY_KEY_SLUG]) ? null : $_GET[self::QUERY_KEY_SLUG];
                    if ($slug) {
                        $source = empty($_GET[self::QUERY_KEY_SOURCE]) ? '' : $_GET[self::QUERY_KEY_SOURCE];
                        $response = [$slug, $source];
                    }
                }
                break;
        }

        return $response;
    }

    /**
     * @param \ClickerVolt\Link $link
     * @return [ $urls, $weights ]
     */
    function getUrlsAndWeights($link)
    {
        if (!empty($_GET[self::QUERY_KEY_URL])) {
            $url = urldecode($_GET[self::QUERY_KEY_URL]);
            $sanitizedURL = filter_var($url, FILTER_SANITIZE_URL);
            if (filter_var($sanitizedURL, FILTER_VALIDATE_URL)) {
                return [[$url], [1.0]];
            }
        }

        $linkSettings = $link->getSettings();
        $urls = $linkSettings[Link::SETTING_DEFAULT_URLS];
        $weights = $linkSettings[Link::SETTING_DEFAULT_WEIGHTS];

        if (!empty($linkSettings[Link::SETTINGS_REDIRECT_RULES])) {
            require_once __DIR__ . '/rules/rulesRouter.php';

            $rulesRouter = new RulesRouter();
            $fulfilledRule = $rulesRouter->getFirstFulfilled($linkSettings[Link::SETTINGS_REDIRECT_RULES]);
            if ($fulfilledRule) {
                $urls = $fulfilledRule['urls'];
                $weights = $fulfilledRule['weights'];
            }
        }

        return [$urls, $weights];
    }


    const QUERY_KEY_SLUG = 's';
    const QUERY_KEY_SOURCE = 'src';
    const QUERY_KEY_URL = 'url';
    const QUERY_KEY_EXTERNAL_ID = 'external-id';
    const QUERY_KEY_VAR_1 = Click::COL_V1;
    const QUERY_KEY_VAR_2 = Click::COL_V2;
    const QUERY_KEY_VAR_3 = Click::COL_V3;
    const QUERY_KEY_VAR_4 = Click::COL_V4;
    const QUERY_KEY_VAR_5 = Click::COL_V5;
    const QUERY_KEY_VAR_6 = Click::COL_V6;
    const QUERY_KEY_VAR_7 = Click::COL_V7;
    const QUERY_KEY_VAR_8 = Click::COL_V8;
    const QUERY_KEY_VAR_9 = Click::COL_V9;
    const QUERY_KEY_VAR_10 = Click::COL_V10;

    private $type;
}
