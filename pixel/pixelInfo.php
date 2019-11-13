<?php

namespace ClickerVolt;

require_once __DIR__ . '/actionHandler.php';

class PixelInfo
{

    /**
     * 
     */
    static function getPixelHTML($defaultCID = '', $defaultType = '', $defaultName = '', $defaultRevenue = '', $defaultSlug = '')
    {

        $url = self::getURL(null, $defaultType, $defaultName, $defaultRevenue, $defaultSlug);
        return "<iframe style='width: 1px; height: 1px;' src='{$url}'></iframe>";
    }

    /**
     * 
     */
    static function getPostbackURL($defaultCID = '', $defaultType = '', $defaultName = '', $defaultRevenue = '')
    {

        return self::getURL($defaultCID, $defaultType, $defaultName, $defaultRevenue);
    }

    /**
     * 
     */
    static function getURL($defaultCID = '', $defaultType = '', $defaultName = '', $defaultRevenue = '', $defaultSlug = '')
    {

        require_once __DIR__ . '/../utils/urlTools.php';

        $cid = ActionHandler::URL_PARAM_CLICK_ID;
        $type = ActionHandler::URL_PARAM_ACTION_TYPE;
        $name = ActionHandler::URL_PARAM_ACTION_NAME;
        $rev = ActionHandler::URL_PARAM_ACTION_REVENUE;
        $slug = ActionHandler::URL_PARAM_SLUG;

        $queries = [];

        if ($defaultSlug) {
            $queries[$slug] = $defaultSlug;
        }

        $queries[$type] = $defaultType;
        $queries[$name] = $defaultName;
        $queries[$rev] = $defaultRevenue;

        if ($defaultCID !== null) {
            $queries[$cid] = $defaultCID;
        }

        foreach ($queries as $k => $query) {
            $queries[$k] = "{$k}=" . urlencode($query);
        }
        $queries = implode('&', $queries);
        return URLTools::getPluginURL() . "/pixel/do.php?{$queries}";
    }
}
