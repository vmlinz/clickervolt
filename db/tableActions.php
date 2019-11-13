<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/../utils/fileTools.php';

class Action implements ArraySerializer
{
    use ArraySerializerImpl;

    const STORAGE_PATH = 'unprocessed_actions';

    private $clickId;
    private $actionType;
    private $actionName;
    private $actionRevenue;
    private $clickTimestamp;
    private $actionTimestamp;
    private $restrictToSlug;

    function __construct($properties = [])
    {
        $this->fromArray($properties);
    }

    function getRevenue()
    {
        return $this->actionRevenue;
    }

    function setClickId($clickId)
    {
        $this->clickId = $clickId;
    }

    function getActionName()
    {
        return $this->actionName;
    }

    function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    function getRestrictToSlug()
    {
        return $this->restrictToSlug;
    }

    function getClickTimestamp()
    {
        return $this->clickTimestamp;
    }

    function setClickTimestamp($timestamp)
    {
        $this->clickTimestamp = $timestamp;
    }

    /**
     * 
     */
    function queue()
    {

        $toQueue = json_encode($this->toArray());

        $file = implode('_', [
            $this->clickId,
            $this->actionType ? md5($this->actionType) : '',
            $this->actionName ? md5($this->actionName) : '',
        ]);

        $path = FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$file}";
        FileTools::atomicSave($path, $toQueue);
    }
}

class TableActions extends Table
{

    const MAX_ACTION_TYPE_LENGTH = 40;
    const MAX_ACTION_NAME_LENGTH = 40;

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_actions');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {

        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $maxTL = self::MAX_ACTION_TYPE_LENGTH;
            $maxNL = self::MAX_ACTION_NAME_LENGTH;

            $sql = "CREATE TABLE {$tableName} (
                        `clickId` char(16) not null,
                        `actionType` varchar({$maxTL}) not null,
                        `actionName` varchar({$maxNL}) not null,
                        `actionRevenue` double not null,
                        `clickTimestamp` int unsigned null,
                        `actionTimestamp` int unsigned null,
                        primary key (`clickId`, `actionType`, `actionName`),
                        key `actionTimestamp_idx` (`actionTimestamp`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) {

            if ($oldVersion < 1.06 && !$this->doesColumnExist('clickTimestamp')) {

                $sql = "ALTER TABLE {$tableName}
                        ADD clickTimestamp INT UNSIGNED NULL,
                        ADD actionTimestamp INT UNSIGNED NULL,
                        ADD KEY `actionTimestamp_idx` (`actionTimestamp`)";

                $res = $wpdb->query($sql);
                if ($res === false) {
                    throw new \Exception("Cannot update table {$tableName}: {$wpdb->last_error}");
                }
            }
        }
    }

    /**
     * 
     * @param array $clickIds
     * @return array
     */
    public function loadAll($clickIds)
    {

        global $wpdb;

        $actions = [];

        if ($clickIds) {

            $format = implode(',', array_fill(0, count($clickIds), '%s'));

            $tableName = $this->getName();
            $rows = $wpdb->get_results(
                $wpdb->prepare("select * from {$tableName} where clickId in ({$format})", $clickIds),
                ARRAY_A
            );

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $actions[] = $this->rowToAction($row);
                }
            }
        }

        return $actions;
    }

    /**
     * 
     */
    private function rowToAction($row)
    {
        return new Action($row);
    }

    /**
     * @param \ClickerVolt\Action[] $actions
     * @throws \Exception
     */
    public function insert($actions)
    {

        $mapper = [
            'clickId' => ['type' => '%s'],
            'actionType' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, self::MAX_ACTION_TYPE_LENGTH);
            }],
            'actionName' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, self::MAX_ACTION_NAME_LENGTH);
            }],
            'actionRevenue' => ['type' => '%f'],
            'clickTimestamp' => ['type' => '%d'],
            'actionTimestamp' => ['type' => '%d'],
        ];

        $updateKeys = [
            'actionRevenue' => 'values(actionRevenue)'
        ];
        parent::insertBulk($actions, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }
}
