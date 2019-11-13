<?php

namespace ClickerVolt;

class JSTracking
{
    static function getRemoteTrackingScript($slug, $options = [])
    {
        require_once __DIR__ . '/../../utils/urlTools.php';
        require_once __DIR__ . '/../../utils/fileTools.php';
        require_once __DIR__ . '/../../utils/dataProxy.php';
        require_once __DIR__ . '/../../db/db.php';
        require_once __DIR__ . '/../../db/objects/cvSettings.php';

        $defaultOptions = [
            'minimize' => false,
            'forceRecache' => false,
            'cloaked' => false,
        ];
        $options = array_merge($defaultOptions, $options);

        $pluginURL = URLTools::getPluginURL();
        $pluginDomain = URLTools::getHost($pluginURL);
        $pluginPath = URLTools::getPath($pluginURL);
        $serverURL = "//#TOKEN_CVTRACK_JS_DOMAIN#{$pluginPath}/redirect/jsTracking/remoteTracking.php";

        $tmpSlug = '#SLUGGISH_SLUG#';
        $path = FileTools::getDataFolderPath('misc') . '/rtjs';
        if ($options['forceRecache'] || !file_exists($path)) {

            $version = DB::VERSION;
            $tokens = [
                '#TOKEN_SLUG#' => $tmpSlug,
                '#TOKEN_REMOTE_TRACKING_SERVER_URL#' => $serverURL,
                '#TOKEN_RECAPTCHA_V3_SITE_KEY#' => CVSettings::get(CVSettings::RECAPTCHA3_SITE_KEY),
                '#TOKEN_RECAPTCHA_V3_HIDE_BADGE#' => CVSettings::get(CVSettings::RECAPTCHA3_HIDE_BADGE),
                '#TOKEN_CVTRACK_JS_URL#' => "//#TOKEN_CVTRACK_JS_DOMAIN#{$pluginPath}/redirect/jsTracking/js/cvTrack.js?v={$version}",
            ];

            $js = file_get_contents(__DIR__ . '/js/remoteTracking.js');
            $js = str_replace(array_keys($tokens), array_values($tokens), $js);

            if ($options['minimize']) {
                $js = self::minimizeContent($js);
            }

            if (false === file_put_contents($path, $js)) {
                throw new \Exception("Cannot write Time-On-Page script to disk");
            }
        } else {
            $js = file_get_contents($path);
            if (empty($js) || !is_string($js)) {
                throw new \Exception("Cannot load Time-On-Page script from disk");
            }
        }

        if ($options['cloaked']) {
            $domain = URLTools::getHost(URLTools::getCurrentURL());
        } else {
            $domain = $pluginDomain;
        }

        $dynamicReplacements = [
            $tmpSlug => $slug,
            '#TOKEN_CVTRACK_JS_DOMAIN#' => $domain,
        ];

        $botTrapURL = "{$serverURL}?action=bt";
        $botTrapURL = str_replace(array_keys($dynamicReplacements), array_values($dynamicReplacements), $botTrapURL);
        $botTrapHTML = "<span style='width:0;height:0;position:absolute;bottom:0;right:0;overflow:hidden;'><a href='{$botTrapURL}'>-</a></span>";

        $js = str_replace(array_keys($dynamicReplacements), array_values($dynamicReplacements), $js);
        return str_replace("'", '"', "{$botTrapHTML}<script>{$js}</script>");
    }

    /**
     * 
     */
    static private function minimizeContent($content)
    {

        require_once __DIR__ . '/../../admin/others/JSqueeze/JSqueeze.php';

        $minifier = new \Patchwork\JSqueeze();
        $content = $minifier->squeeze($content);

        // Remove spaces from start of lines
        $aLines = [];
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
            $aLines[] = preg_replace('/^ +/', '', $line);
        }
        $content = implode('', $aLines);

        // Some replacements
        $aFromTo = [
            "= " => "=",
            " =" => "=",
            "+ " => "+",
            " +" => "+",
            "; " => ";",
            " ;" => ";"
        ];

        $aFrom = array_keys($aFromTo);
        $aTo = array_values($aFromTo);

        do {
            $newContent = str_replace($aFrom, $aTo, $content);

            if ($newContent === $content) {
                break;
            }

            $content = $newContent;
        } while (true);

        // Remove excess spaces
        $newContent = preg_replace('/\s\s+/', ' ', $newContent);

        return $newContent;
    }
}
