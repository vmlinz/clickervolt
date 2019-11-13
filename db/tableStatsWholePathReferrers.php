<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStats.php';

TableStats::registerClass('ClickerVolt\\TableStatsWholePathReferrers');
class TableStatsWholePathReferrers extends TableStatsWholePath
{

    public function getName()
    {
        return $this->wpTableName('clickervolt_stats_whole_path_referrers');
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

        return array_merge(parent::getColumns(), ['referrerHash' => 'binary(16) not null']);
    }

    /**
     * 
     */
    public function getInsertMapper()
    {

        return array_merge(parent::getInsertMapper(), [
            'referrerHash' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? hex2bin($data) : self::UNKNOWN;
            }],
        ]);
    }
}
