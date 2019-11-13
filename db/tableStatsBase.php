<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStats.php';

TableStats::registerClass('ClickerVolt\\TableStatsBase');
class TableStatsBase extends TableStats
{

    public function getName()
    {
        return $this->wpTableName('clickervolt_stats_base');
    }

    /**
     * 
     * @return array 
     * [
     *   "columnName" => "mysql type definition",
     *   "columnName" => "mysql type definition",
     *   etc...
     * ]
     */
    public function getColumns()
    {

        return [];
    }

    /**
     * 
     */
    public function getInsertMapper()
    {

        return [];
    }
}
