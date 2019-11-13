<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/handlerWholePath.php';

use ClickerVolt\TableURLsPaths;

class HandlerClicks extends HandlerWholePath
{
    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {
        $tableClicksName = (new \ClickerVolt\TableClicks())->getName();
        $tableAIDsName = (new \ClickerVolt\TableAIDs())->getName();
        $tableActionsName = (new \ClickerVolt\TableActionsSummary())->getName();
        $tableSuspiciousClicks = (new \ClickerVolt\TableSuspiciousClicks())->getName();

        $tables = [
            $tableClicksName,
            "{$tableAIDsName} as aids on aids.clickId = {$tableClicksName}.id",
            "{$tableSuspiciousClicks} as suspiciousClicks on suspiciousClicks.clickId = {$tableClicksName}.id",
            "{$tableActionsName} as actions on actions.clickId = {$tableClicksName}.id",
        ];

        $mapper = $this->getMapper($request);
        $devicesTableNeeded = false;
        $geosTableNeeded = false;
        $referrersTableNeeded = false;

        foreach ($request->segments as $segment) {
            if (strpos($mapper[$segment->getType()][self::MAP_SELECT], 'devices.') !== false) {
                $devicesTableNeeded = true;
            }
            if (strpos($mapper[$segment->getType()][self::MAP_SELECT], 'geos.') !== false) {
                $geosTableNeeded = true;
            }
            if (strpos($mapper[$segment->getType()][self::MAP_SELECT], 'ref.') !== false) {
                $referrersTableNeeded = true;
            }
        }

        if ($devicesTableNeeded) {
            $tableDevices = new \ClickerVolt\TableDevices();
            $tables[] = $tableDevices->getName() . " as devices on devices.deviceHash = {$tableClicksName}.deviceHash";
        }

        if ($geosTableNeeded) {
            $tableGeos = new \ClickerVolt\TableGeos();
            $tables[] = $tableGeos->getName() . " as geos on geos.geoHash = {$tableClicksName}.geoHash";
        }

        if ($referrersTableNeeded) {
            $tableReferrers = new \ClickerVolt\TableReferrers();
            $tables[] = $tableReferrers->getName() . " as ref on ref.referrerHash = {$tableClicksName}.referrerHash";
        }

        if ($request->isSegmentPresent(Segment::TYPE_FUNNEL_LINK)) {

            $tableURLsPaths = new TableURLsPaths();
            $name = $tableURLsPaths->getName();
            $tables[] = "{$name} as urlsPaths on urlsPaths.hash = {$tableClicksName}.urlsPathHash";
        }

        return $tables;
    }

    /**
     * 
     */
    protected function formatValue($value, $segmentType)
    {
        $value = parent::formatValue($value, $segmentType);

        $otherHandlers = [];
        $otherHandlers[] = new HandlerWholePathDevices();
        $otherHandlers[] = new HandlerWholePathGeos();
        $otherHandlers[] = new HandlerWholePathReferrers();
        foreach ($otherHandlers as $handler) {
            $value = $handler->formatValue($value, $segmentType);
        }

        return $value;
    }

    /**
     * 
     */
    protected function getTimestampColumn()
    {
        return "timestamp";
    }

    /**
     * 
     */
    protected function getSQLMetrics($request)
    {
        $table = new \ClickerVolt\TableClicks();
        $tname = $table->getName();

        return "count({$tname}.id) as clicks,
                sum({$tname}.isUnique) as clicksUnique,
                sum(actions.actionsRevenue) as revenue,
                sum(actions.actionsCount) as actions,
                sum(aids.hasAttention) as hasAttention,
                sum(aids.hasInterest) as hasInterest,
                sum(aids.hasDesire) as hasDesire,
                sum(suspiciousClicks.score) as suspiciousScoreSum
                ";
    }

    /**
     * 
     * @return []
     */
    protected function getMapper($request)
    {
        $mapper = [
            Segment::TYPE_SUSPICIOUS_VS_CLEAN => [
                self::MAP_SELECT => "n/a"    // too slow - return unknown
            ],

            Segment::TYPE_SUSPICIOUS_BUCKETS => [
                self::MAP_SELECT => "n/a"    // too slow - return unknown
            ],

            Segment::TYPE_IP_RANGE_C => [
                self::MAP_SELECT => "if( is_ipv4(inet6_ntoa(ip)), 
                                        if( inet6_ntoa(ip) = '0.0.0.0', null, concat(substring_index(inet_ntoa(inet_aton(inet6_ntoa(ip)) & 0xffffff00), '.', 3), '.xxx')), 
                                        concat_ws(':', left(hex(ip), 4), right(left(hex(ip), 8), 4), right(left(hex(ip), 12), 4), ':0') )"
            ],

            Segment::TYPE_IP_RANGE_B => [
                self::MAP_SELECT => "if( is_ipv4(inet6_ntoa(ip)), 
                                        if( inet6_ntoa(ip) = '0.0.0.0', null, concat(substring_index(inet_ntoa(inet_aton(inet6_ntoa(ip)) & 0xffffff00), '.', 2), '.xxx.xxx')), 
                                        concat_ws(':', left(hex(ip), 4), right(left(hex(ip), 8), 4), ':0') )"
            ],

            Segment::TYPE_VAR_1 => [
                self::MAP_SELECT => "v1"
            ],

            Segment::TYPE_VAR_2 => [
                self::MAP_SELECT => "v2"
            ],

            Segment::TYPE_VAR_3 => [
                self::MAP_SELECT => "v3"
            ],

            Segment::TYPE_VAR_4 => [
                self::MAP_SELECT => "v4"
            ],

            Segment::TYPE_VAR_5 => [
                self::MAP_SELECT => "v5"
            ],

            Segment::TYPE_VAR_6 => [
                self::MAP_SELECT => "v6"
            ],

            Segment::TYPE_VAR_7 => [
                self::MAP_SELECT => "v7"
            ],

            Segment::TYPE_VAR_8 => [
                self::MAP_SELECT => "v8"
            ],

            Segment::TYPE_VAR_9 => [
                self::MAP_SELECT => "v9"
            ],

            Segment::TYPE_VAR_10 => [
                self::MAP_SELECT => "v10"
            ],
        ];

        $handleDevices = new HandlerWholePathDevices();
        $handleGeos = new HandlerWholePathGeos();
        $handleReferrers = new HandlerWholePathReferrers();

        return array_merge(
            $handleDevices->getMapper($request),
            $handleGeos->getMapper($request),
            $handleReferrers->getMapper($request),
            parent::getMapper($request),
            $mapper
        );
    }
}
