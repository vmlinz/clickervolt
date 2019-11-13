<?php

namespace ClickerVolt\Reporting;

use ClickerVolt\TableStats;


require_once __DIR__ . '/handlerBase.php';

class HandlerWholePathReferrers extends HandlerWholePath
{

    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {

        $table = new \ClickerVolt\TableStatsWholePathReferrers();
        $tableName = $table->getName();

        $tableReferrers = new \ClickerVolt\TableReferrers();

        $tables = [$tableName, $tableReferrers->getName() . " as ref on ref.referrerHash = {$tableName}.referrerHash"];
        return $this->addPathsTables($request, $tableName, $tables);
    }

    /**
     * 
     * @return []
     */
    public function getMapper($request)
    {

        $mapper = [

            Segment::TYPE_REFERRER => [
                self::MAP_SELECT => 'ref.referrer'
            ],

            Segment::TYPE_REFERRER_DOMAIN => [
                self::MAP_SELECT => "if(ref.referrer is null, null, substring_index(substring_index(substring_index(substring_index(ref.referrer, '/', 3), '://', -1), '/', 1), '?', 1))"
            ],
        ];

        return array_merge(parent::getMapper($request), $mapper);
    }
}
