<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/objects/suspiciousClick.php';

class TableSuspiciousClicks extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_suspicious_clicks');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {
        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $sql = "CREATE TABLE {$tableName} (
                        `clickId` char(16) not null,
                        `score` tinyint unsigned not null,
                        primary key (`clickId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }


    /**
     * 
     * @param array $clickIds
     * @return array
     */
    public function load($clickIds)
    {
        global $wpdb;

        $suspiciousClicks = [];

        if ($clickIds) {

            $format = implode(',', array_fill(0, count($clickIds), '%s'));

            $tableName = $this->getName();
            $rows = $wpdb->get_results(
                $wpdb->prepare("select * from {$tableName} where clickId in ({$format})", $clickIds),
                ARRAY_A
            );

            foreach ($rows as $row) {
                $sc = new SuspiciousClick($row['clickId'], $row['score']);
                $suspiciousClicks[$sc->getClickId()] = $sc;
            }
        }

        return $suspiciousClicks;
    }

    /**
     * @param \ClickerVolt\SuspiciousClick[] $suspiciousClicks
     * @throws \Exception
     */
    public function insert($suspiciousClicks)
    {
        $mapper = [
            'clickId' => ['type' => '%s'],
            'score' => ['type' => '%d'],
        ];

        $updateKeys = [
            '`score`' => 'values(`score`)',
        ];
        parent::insertBulk($suspiciousClicks, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }
}
