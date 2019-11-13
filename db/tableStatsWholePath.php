<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStats.php';
require_once __DIR__ . '/tableLinks.php';

TableStats::registerClass('ClickerVolt\\TableStatsWholePath');
class TableStatsWholePath extends TableStats
{

    public function getName()
    {
        return $this->wpTableName('clickervolt_stats_whole_path');
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

        return [
            'linkId' => 'int unsigned not null',
            'linkIdsPath' => 'varchar(255) not null',
            'urlsPathHash' => 'binary(16) not null',
            'source' => 'varchar(40) not null',
            'url' => 'varchar(255) not null',
        ];
    }

    /**
     * 
     */
    public function getInsertMapper()
    {

        return [
            'linkId' => ['type' => '%d'],
            'linkIdsPath' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 255) : '';
            }],
            'urlsPathHash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'source' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, 40);
            }],
            'url' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, 255);
            }],
        ];
    }

    /**
     * 
     */
    protected function getPartitionCreationClause()
    {
        $nbPartitions = 8;
        return "PARTITION BY HASH(linkId % {$nbPartitions}) PARTITIONS {$nbPartitions}";
    }
}
