<?php

namespace ClickerVolt;

use ClickerVolt\Reporting\Segment;


require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/../cron.php';
require_once __DIR__ . '/../reporting/drilldown.php';
require_once __DIR__ . '/../reporting/clicklog.php';
require_once __DIR__ . '/../../db/tableLinks.php';
require_once __DIR__ . '/../../utils/countryCodes.php';
require_once __DIR__ . '/../../utils/duration.php';
require_once __DIR__ . '/../../utils/ipTools.php';
require_once __DIR__ . '/../../utils/fileTools.php';
require_once __DIR__ . '/../../others/device-detector/Parser/Client/Browser.php';
require_once __DIR__ . '/../../others/device-detector/Parser/OperatingSystem.php';

class AjaxStats extends Ajax
{

    const OPTION_INCLUDE_SLUGS_WITHOUT_TRAFFIC = 'all-slugs';
    const OPTION_SEGMENT_ICONS = 'segment-icons';

    const OPTION_SEGMENT_ICONS_WHICH_SEGMENT = 'segment';
    const OPTION_SEGMENT_ICONS_DETAILS = 'details';
    const OPTION_SEGMENT_ICONS_DETAILS_WHICH_ICON = 'icon';
    const OPTION_SEGMENT_ICONS_DETAILS_WHICH_TITLE = 'title';
    const OPTION_SEGMENT_ICONS_DETAILS_WHICH_CALLBACK = 'segcback';

    static function processClicksQueue()
    {
        // Force refresh of clicks queue
        Cron::processClicksQueue();
    }

    /**
     * 
     */
    static function getStats()
    {
        $rows = [];

        $defaultTZ = date_default_timezone_get();
        date_default_timezone_set('UTC');

        try {
            $options = empty($_POST['options']) ? [] : $_POST['options'];
            $addSlugsWithoutTraffic = empty($options[self::OPTION_INCLUDE_SLUGS_WITHOUT_TRAFFIC]) ? false : true;

            $request = self::getRequestFromPOST();
            $drilldown = new \ClickerVolt\Reporting\Drilldown($request);
            $rows = $drilldown->getRows();

            $slugKey = null;
            $urlKey = null;
            foreach ($request->segments as $i => $segment) {
                if ($segment->getType() == \ClickerVolt\Reporting\Segment::TYPE_LINK) {
                    $slugKey = "segment{$i}";
                } else if ($segment->getType() == \ClickerVolt\Reporting\Segment::TYPE_URL) {
                    $urlKey = "segment{$i}";
                }
            }

            if ($addSlugsWithoutTraffic) {

                $emptyRow = \ClickerVolt\Reporting\Handler::getEmptyRow($request);

                if ($slugKey !== null) {

                    $slugsWithTraffic = [];
                    foreach ($rows as $row) {
                        $slug = $row[$slugKey];
                        $slugsWithTraffic[$slug] = $slug;
                    }

                    $tableLinks = new \ClickerVolt\TableLinks();
                    $allLinks = $tableLinks->loadAll(['slug', 'settings']);
                    foreach ($allLinks as $slug => $link) {
                        if (empty($slugsWithTraffic[$slug])) {
                            $newRow = $emptyRow;
                            foreach ($newRow as $k => $v) {
                                $newRow[$k] = 0;
                            }
                            $newRow[$slugKey] = $slug;

                            if ($urlKey !== null) {
                                $linkSettings = $link->getSettings();
                                foreach ($linkSettings[Link::SETTING_DEFAULT_URLS] as $i => $url) {
                                    if ($i == 0) {
                                        $newRow[$urlKey] = $url;
                                    } else {
                                        $anotherRow = $emptyRow;
                                        $anotherRow[$slugKey] = $slug;
                                        $anotherRow[$urlKey] = $url;
                                        $rows[] = $anotherRow;
                                    }
                                }
                            }

                            $rows[] = $newRow;
                        }
                    }
                }
            }

            // if( $slugKey !== null ) {
            //     usort( $rows, function( $row1, $row2 ) {

            //     });
            // }

            return $rows;
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            date_default_timezone_set($defaultTZ);
        }
    }

    static private function getIconURL($relativePath, $iconName, $iconIfNotFound = 'UNK')
    {
        $path = FileTools::getPluginFolderPath($relativePath) . "/{$iconName}.png";
        if (!file_exists($path)) {
            $iconName = $iconIfNotFound;
        }
        $clickerVoltBaseFolder = FileTools::getPluginFolderName();
        return plugins_url() . "/{$clickerVoltBaseFolder}/{$relativePath}/{$iconName}.png";
    }

    /**
     * 
     */
    static function getClickLog()
    {
        $rows = [
            'set' => [],
            'add' => [],
        ];

        $defaultTZ = date_default_timezone_get();
        date_default_timezone_set('UTC');

        try {
            self::processClicksQueue();

            $request = self::getRequestFromPOST();
            $rowsCount = Sanitizer::sanitizeKey($_POST['length']);
            $tzOffset = Sanitizer::sanitizeKey($_POST['timezoneOffset']);

            $ua = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
            $ip = IPTools::getUserIP();
            $machine = md5("{$ua}-{$ip}");
            $lastClickIdCacheKey = "clickervolt-clicklog-lastClickId-{$machine}";
            $currentRowsCount = Sanitizer::sanitizeKey($_POST['currentRowsCount']);
            if ($currentRowsCount) {
                $lastClickId = get_transient($lastClickIdCacheKey);
                if ($lastClickId) {
                    $request->segments[] = new \ClickerVolt\Reporting\Segment(\ClickerVolt\Reporting\Segment::TYPE_CLICK_ID, $lastClickId);
                }
            }

            $clicklog = new \ClickerVolt\Reporting\ClickLog($request);
            $sqlRows = $clicklog->getRows($rowsCount);

            $browserNamesToCodes = array_flip(\DeviceDetector\Parser\Client\Browser::getAvailableBrowsers());
            $osNamesToCodes = array_flip(\DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystems());

            $duration = new Duration();

            if (!isset($lastClickId)) {
                $lastClickId = 0;
            }
            $maxClickId = $lastClickId;
            foreach ($sqlRows as $i => $sqlRow) {

                $clickId = $sqlRow['id'];
                $maxClickId = max($clickId, $maxClickId);

                $date = date("Y-m-d H:i:s", $sqlRow['eventTime'] - ($tzOffset * 60));
                $slug = $sqlRow['slug'];
                $url = $sqlRow['url'];
                $countryCode = $sqlRow['geoCountry'];
                $isp = $sqlRow['geoIsp'];
                $deviceBrowser = $sqlRow['deviceBrowser'];
                $deviceOS = $sqlRow['deviceOS'];
                $deviceOSVersion = $sqlRow['deviceOSVersion'];
                $isUnique = $sqlRow['isUnique'];
                $ip = $sqlRow['ip'];
                $source = $sqlRow['source'];
                $referrer = $sqlRow['referrer'];
                $actionRevenue = $sqlRow['actionRevenue'];
                $timeToAction = $sqlRow['timeToAction'];
                $v1 = $sqlRow['v1'];
                $v2 = $sqlRow['v2'];
                $v3 = $sqlRow['v3'];
                $v4 = $sqlRow['v4'];
                $v5 = $sqlRow['v5'];
                $v6 = $sqlRow['v6'];
                $v7 = $sqlRow['v7'];
                $v8 = $sqlRow['v8'];
                $v9 = $sqlRow['v9'];
                $v10 = $sqlRow['v10'];

                $clickerVoltBaseFolder = FileTools::getPluginFolderName();

                if ($countryCode) {
                    $cc = str_replace('uk', 'gb', strtolower($countryCode));
                    $cn = str_replace("'", "-", CountryCodes::MAP[$countryCode]);
                    $iconPath = plugins_url() . "/{$clickerVoltBaseFolder}/admin/images/icons/flags/{$cc}.png";
                    $countryIcon = "<span class='countries'><img src='{$iconPath}' title='{$cn}'></span>";
                } else {
                    $countryIcon = "";
                }

                $iconURL = self::getIconURL('admin/images/icons/browsers', empty($browserNamesToCodes[$deviceBrowser]) ? '' : $browserNamesToCodes[$deviceBrowser]);
                $browserIcon = "<span class='browser'><img src='{$iconURL}' title='{$deviceBrowser}'></span>";

                $iconURL = self::getIconURL('admin/images/icons/os', empty($osNamesToCodes[$deviceOS]) ? '' : $osNamesToCodes[$deviceOS]);
                $osIcon = "<span class='os'><img src='{$iconURL}' title='{$deviceOS} {$deviceOSVersion}'></span>";

                $returningVisitorIcon = $isUnique ? '' : "<i class='material-icons returning-visitor' title='Returning Visitor'></i>";

                if ($referrer) {
                    $parts = parse_url($referrer);
                    $referrer = "{$parts['host']} <a href='{$referrer}' target='_blank' title='{$referrer}'><i class='material-icons url'></i></a>";
                }

                $slug = "{$slug} <a href='{$url}' target='_blank' title='{$url}'><i class='material-icons url'></i></a>";

                $row = [
                    $date, $clickId, $slug,
                    implode(' ', [$countryIcon, $browserIcon, $osIcon, $returningVisitorIcon]),
                    $isp, $ip, $source, $referrer, $actionRevenue, $duration->humanize($timeToAction),
                    $v1, $v2, $v3, $v4, $v5, $v6, $v7, $v8, $v9, $v10
                ];

                if ($lastClickId) {
                    $rows['add'][] = $row;
                } else {
                    $rows['set'][] = $row;
                }
            }

            if ($maxClickId > $lastClickId) {
                set_transient($lastClickIdCacheKey, $maxClickId, MINUTE_IN_SECONDS);
            }

            return $rows;
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            date_default_timezone_set($defaultTZ);
        }
    }

    /**
     * @return \ClickerVolt\Reporting\Request
     */
    static private function getRequestFromPOST()
    {
        $segments = empty($_POST['segments']) ? [] : Sanitizer::sanitizeTextField($_POST['segments']);
        $linkIdFilter = empty($_POST['linkIdFilter']) ? null : Sanitizer::sanitizeKey($_POST['linkIdFilter']);
        $sourceIdFilter = empty($_POST['sourceIdFilter']) ? null : Sanitizer::sanitizeKey($_POST['sourceIdFilter']);
        $dateStart = empty($_POST['dateStart']) ? 'today UTC' : Sanitizer::sanitizeTextField($_POST['dateStart']);
        $dateEnd = empty($_POST['dateEnd']) ? 'today UTC' : Sanitizer::sanitizeTextField($_POST['dateEnd']);
        $allAggregated = false;

        if (in_array($linkIdFilter, \ClickerVolt\Reporting\Request::$linksAllOptions)) {
            if ($linkIdFilter == \ClickerVolt\Reporting\Request::LINKS_ALL_AGGREGATED) {
                $allAggregated = true;
            }
            $linkIdFilter = null;
        }

        $request = new \ClickerVolt\Reporting\Request();
        $request->fromTimestamp = strtotime($dateStart);
        $request->toTimestamp = strtotime("{$dateEnd} +1day");
        $request->segments = [];
        $request->allAggregated = $allAggregated;
        $request->linkIdFilters = $linkIdFilter ? [$linkIdFilter] : [];
        $request->sourceIdFilters = $sourceIdFilter ? [$sourceIdFilter] : [];

        foreach ($segments as $k => $segment) {

            if (!is_array($segment)) {
                $segment = [
                    'type' => $segment,
                    'filter' => null
                ];
                $segments[$k] = $segment;
            }

            $request->segments[] = new \ClickerVolt\Reporting\Segment($segment['type'], $segment['filter']);
        }

        return $request;
    }
};
