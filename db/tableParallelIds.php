<?php

namespace ClickerVolt;

require_once __DIR__ . '/objects/parallelId.php';

class TableParallelIds extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_parallel_ids');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {
        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {
            $maxPID = ParallelId::MAX_PID_LENGTH;

            $sql = "CREATE TABLE {$tableName} (
                        `parallelId` varchar({$maxPID}) not null,
                        `clickId` char(16) not null,
                        primary key (`parallelId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param string $pid
     * @return \ClickerVolt\ParallelId
     */
    public function load($pid)
    {
        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results(
            $wpdb->prepare("select * from {$tableName} where parallelId = %s", $pid),
            ARRAY_A
        );

        if (!empty($rows)) {
            $parallelId = $this->rowToParallelId($rows[0]);
        } else {
            $parallelId = null;
        }

        return $parallelId;
    }

    /**
     * @param array $row
     * @return \ClickerVolt\ParallelId
     */
    public function rowToParallelId($row)
    {
        return new ParallelId($row);
    }

    /**
     * @param \ClickerVolt\ParallelId[] $parallelIds
     * @throws \Exception
     */
    public function insert($parallelIds)
    {
        $mapper = [
            'parallelId' => ['type' => '%s'],
            'clickId' => ['type' => '%s'],
        ];

        parent::insertBulk($parallelIds, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
