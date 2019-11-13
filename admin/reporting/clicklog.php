<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/request.php';

class ClickLog
{

    /**
     * @param ClickerVolt\Reporting\Request $request
     */
    function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return [][]
     */
    function getRows($count = 50)
    {
        global $wpdb;

        try {
            $prevTimezone = \ClickerVolt\DB::singleton()->setTimezone('+0:00');
            $conditions = [];

            // If the request has a click id segment, along with a click id filter, 
            // then the log will only return the clicks that are more recent than this click id filter
            foreach ($this->request->segments as $segment) {
                if ($segment->getType() == Segment::TYPE_CLICK_ID && !empty($segment->getFilter())) {
                    $clickId = $segment->getFilter();
                    $conditions[] = "clicks.id > '{$clickId}'";
                }
            }
            $conditionsStr = empty($conditions) ? '' : 'where ' . implode(' and ', $conditions);

            $clicksTableName = (new \ClickerVolt\TableClicks())->getName();
            $linksTableName = (new \ClickerVolt\TableLinks())->getName();
            $geosTableName = (new \ClickerVolt\TableGeos())->getName();
            $devicesTableName = (new \ClickerVolt\TableDevices())->getName();
            $sourcesTableName = (new \ClickerVolt\TableSourceTemplates())->getName();
            $referrersTableName = (new \ClickerVolt\TableReferrers())->getName();
            $actionsTableName = (new \ClickerVolt\TableActions())->getName();

            // We get this report in 3 passes as MySQL creates a temp. table
            // when done in one single pass.
            // Step1: Getting latest clicks, without conversion data...
            // Step2: Getting latest conversions, more recent than oldest obtained click from step 1
            // Step3: Post-process: Merge rows, sort by eventTime and crop to requested rows limit

            // Kepts as reference: 1-pass version
            // $sql = "select
            //             clicks.id,
            //             coalesce(actions.actionTimestamp, clicks.`timestamp`) as eventTime,
            //             links.slug,
            //             clicks.url,
            //             geos.geoCountry,
            //             geos.geoIsp,
            //             devices.deviceBrowser,
            //             devices.deviceOS,
            //             devices.deviceOSVersion,
            //             clicks.isUnique,
            //             inet6_ntoa(clicks.ip) as ip,
            //             coalesce(sources.sourceName, clicks.source) as source,
            //             referrers.referrer,
            //             actions.actionRevenue,
            //             if( actions.actionTimestamp is not null, actions.actionTimestamp - clicks.`timestamp`, '' ) as timeToAction,
            //             clicks.v1,
            //             clicks.v2,
            //             clicks.v3,
            //             clicks.v4,
            //             clicks.v5,
            //             clicks.v6,
            //             clicks.v7,
            //             clicks.v8,
            //             clicks.v9,
            //             clicks.v10
            //         from {$clicksTableName} clicks
            //         join {$linksTableName} links on links.id = clicks.linkId
            //         left join {$geosTableName} geos on geos.geoHash = clicks.geoHash
            //         left join {$devicesTableName} devices on devices.deviceHash = clicks.deviceHash
            //         left join {$sourcesTableName} sources on sources.sourceId = clicks.source
            //         left join {$referrersTableName} referrers on referrers.referrerHash = clicks.referrerHash
            //         left join {$actionsTableName} actions on actions.clickId = clicks.id
            //         {$conditionsStr}
            //         order by clicks.id desc 
            //         limit {$count}";

            $clicksSQL = "select
                                clicks.id,
                                clicks.`timestamp` as eventTime,
                                links.slug,
                                clicks.url,
                                geos.geoCountry,
                                geos.geoIsp,
                                devices.deviceBrowser,
                                devices.deviceOS,
                                devices.deviceOSVersion,
                                clicks.isUnique,
                                inet6_ntoa(clicks.ip) as ip,
                                coalesce(sources.sourceName, clicks.source) as source,
                                referrers.referrer,
                                null as actionRevenue,
                                '' as timeToAction,
                                clicks.v1,
                                clicks.v2,
                                clicks.v3,
                                clicks.v4,
                                clicks.v5,
                                clicks.v6,
                                clicks.v7,
                                clicks.v8,
                                clicks.v9,
                                clicks.v10
                            from {$clicksTableName} clicks
                            join {$linksTableName} links on links.id = clicks.linkId
                            left join {$geosTableName} geos on geos.geoHash = clicks.geoHash
                            left join {$devicesTableName} devices on devices.deviceHash = clicks.deviceHash
                            left join {$sourcesTableName} sources on sources.sourceId = clicks.source
                            left join {$referrersTableName} referrers on referrers.referrerHash = clicks.referrerHash
                            {$conditionsStr}
                            order by clicks.id desc 
                            limit {$count}";

            $rows = $wpdb->get_results($clicksSQL, ARRAY_A);
            if (!$rows && !empty($wpdb->last_error)) {
                require_once __DIR__ . '/../../utils/logger.php';
                \ClickerVolt\Logger::getErrorLogger()->log($wpdb->last_error);
            }

            if (!empty($rows)) {
                $oldestRow = $rows[count($rows) - 1];
                $oldestClickTime = $oldestRow['eventTime'];
                $conditions[] = "actions.actionTimestamp >= {$oldestClickTime}";
                $conditionsStr = empty($conditions) ? '' : 'where ' . implode(' and ', $conditions);

                $actionsSQL = "select
                                    actions.clickId as id,
                                    actions.actionTimestamp as eventTime,
                                    links.slug,
                                    clicks.url,
                                    geos.geoCountry,
                                    geos.geoIsp,
                                    devices.deviceBrowser,
                                    devices.deviceOS,
                                    devices.deviceOSVersion,
                                    clicks.isUnique,
                                    inet6_ntoa(clicks.ip) as ip,
                                    coalesce(sources.sourceName, clicks.source) as source,
                                    referrers.referrer,
                                    actions.actionRevenue,
                                    (actions.actionTimestamp - actions.clickTimestamp) as timeToAction,
                                    clicks.v1,
                                    clicks.v2,
                                    clicks.v3,
                                    clicks.v4,
                                    clicks.v5,
                                    clicks.v6,
                                    clicks.v7,
                                    clicks.v8,
                                    clicks.v9,
                                    clicks.v10
                                from {$actionsTableName} actions
                                join {$clicksTableName} clicks on clicks.id = actions.clickId
                                join {$linksTableName} links on links.id = clicks.linkId
                                left join {$geosTableName} geos on geos.geoHash = clicks.geoHash
                                left join {$devicesTableName} devices on devices.deviceHash = clicks.deviceHash
                                left join {$sourcesTableName} sources on sources.sourceId = clicks.source
                                left join {$referrersTableName} referrers on referrers.referrerHash = clicks.referrerHash
                                {$conditionsStr}
                                limit {$count}";

                $actionRows = $wpdb->get_results($actionsSQL, ARRAY_A);
                if (!$actionRows && !empty($wpdb->last_error)) {
                    require_once __DIR__ . '/../../utils/logger.php';
                    \ClickerVolt\Logger::getErrorLogger()->log($wpdb->last_error);
                }

                $rows = array_merge($rows, $actionRows);
                usort($rows, function ($a, $b) {
                    if ($a['eventTime'] < $b['eventTime'])
                        return +1;
                    else if ($a['eventTime'] > $b['eventTime'])
                        return -1;
                    return 0;
                });

                $rows = array_slice($rows, 0, $count);
            }

            return $rows;
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            \ClickerVolt\DB::singleton()->setTimezone($prevTimezone);
        }

        throw new \Exception("Could not get click log for request: " . json_encode($this->request));
    }

    private $request;
}
