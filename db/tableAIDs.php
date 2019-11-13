<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/../utils/fileTools.php';

class AID implements ArraySerializer
{
    use ArraySerializerImpl;

    const STORAGE_PATH = 'unprocessed_aids';

    private $clickId;
    private $hasAttention;
    private $hasInterest;
    private $hasDesire;

    function __construct($properties)
    {
        $this->fromArray($properties);
    }

    function getClickId()
    {
        return $this->clickId;
    }

    function setClickId($clickId)
    {
        $this->clickId = $clickId;
    }

    /**
     * 
     */
    function queue()
    {
        $toQueue = json_encode($this->toArray());

        $file = implode('_', [
            $this->clickId,
        ]);

        $path = FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$file}";
        FileTools::atomicSave($path, $toQueue);
    }
}

class TableAIDs extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_aids');
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
                        `hasAttention` tinyint unsigned not null,
                        `hasInterest` tinyint unsigned not null,
                        `hasDesire` tinyint unsigned not null,
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

        $aids = [];

        if ($clickIds) {

            $format = implode(',', array_fill(0, count($clickIds), '%s'));

            $tableName = $this->getName();
            $rows = $wpdb->get_results(
                $wpdb->prepare("select * from {$tableName} where clickId in ({$format})", $clickIds),
                ARRAY_A
            );

            foreach ($rows as $row) {
                $aid = new AID($row);
                $aids[$aid->getClickId()] = $aid;
            }
        }

        return $aids;
    }

    /**
     * @param \ClickerVolt\AID[] $aids
     * @throws \Exception
     */
    public function insert($aids)
    {
        $mapper = [
            'clickId' => ['type' => '%s'],
            'hasAttention' => ['type' => '%d', 'filter' => function ($data) {
                return $data ? 1 : 0;
            }],
            'hasInterest' => ['type' => '%d', 'filter' => function ($data) {
                return $data ? 1 : 0;
            }],
            'hasDesire' => ['type' => '%d', 'filter' => function ($data) {
                return $data ? 1 : 0;
            }],
        ];

        $updateKeys = [
            'hasAttention' => 'values(hasAttention)',
            'hasInterest' => 'values(hasInterest)',
            'hasDesire' => 'values(hasDesire)',
        ];
        parent::insertBulk($aids, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }
}
