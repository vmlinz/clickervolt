<?php

namespace ClickerVolt;

require_once __DIR__ . '/objects/externalId.php';

class TableExternalIds extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_external_ids');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {
        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {
            $maxEID = ExternalId::MAX_EID_LENGTH;

            $sql = "CREATE TABLE {$tableName} (
                        `clickId` char(16) not null,
                        `externalId` varchar({$maxEID}) not null,
                        primary key (`clickId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param string $clickId
     * @return \ClickerVolt\ExternalId
     */
    public function load($clickId)
    {
        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results(
            $wpdb->prepare("select * from {$tableName} where clickId = %s", $clickId),
            ARRAY_A
        );

        if (!empty($rows)) {
            $externalId = $this->rowToExternalId($rows[0]);
        } else {
            $externalId = null;
        }

        return $externalId;
    }

    /**
     * @param array $row
     * @return \ClickerVolt\ExternalId
     */
    public function rowToExternalId($row)
    {
        return new ExternalId($row['clickId'], $row['externalId']);
    }

    /**
     * @param \ClickerVolt\ExternalId[] $externalIds
     * @throws \Exception
     */
    public function insert($externalIds)
    {
        $mapper = [
            'clickId' => ['type' => '%s'],
            'externalId' => ['type' => '%s'],
        ];

        parent::insertBulk($externalIds, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
