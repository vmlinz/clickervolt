<?php

namespace ClickerVolt;

require_once __DIR__ . '/redirector.php';
require_once __DIR__ . '/../../utils/arrayVars.php';

// Double Meta Refresh

class RedirectorDMR implements Redirector
{
    const KEY_FINAL_REDIRECT_URL = "goto";
    const KEY_COUNTER = "cnt";

    /**
     * 
     */
    function redirectTo($url, $options = [])
    {
        require_once __DIR__ . '/../../utils/urlTools.php';

        $encodedUrl = bin2hex($url);
        $urlToHere = URLTools::getPluginURL() . '/redirect/redirectors/dmr.php?' . self::KEY_FINAL_REDIRECT_URL . '=' . $encodedUrl;
        self::metaRefresh($urlToHere);
    }

    static function metaRefresh($url)
    {
        $url = str_replace("'", "%27", $url);
        echo "<meta name='referrer' content='no-referrer' /><meta http-equiv='refresh' content='0; url={$url}'>";
        exit;
    }
}

$encodedUrl = ArrayVars::queryGet(RedirectorDMR::KEY_FINAL_REDIRECT_URL);
if (!empty($encodedUrl)) {
    $url = hex2bin($encodedUrl);
    if ($url) {
        RedirectorDMR::metaRefresh($url);
    }
}
