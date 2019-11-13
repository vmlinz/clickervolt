<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/tableActions.php';

class ActionSummary implements ArraySerializer
{

    use ArraySerializerImpl;

    private $clickId;
    private $actionsCount;
    private $actionsRevenue;

    function __construct($clickId, $actionsCount, $actionsRevenue)
    {
        $this->clickId = $clickId;
        $this->actionsCount = $actionsCount;
        $this->actionsRevenue = $actionsRevenue;
    }

    function addActions($addCount, $addRevenue)
    {
        $this->actionsCount += $addCount;
        $this->actionsRevenue += $addRevenue;
    }
}

class TableActionsSummary extends Table
{

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_actions_summary');
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
                        `actionsCount` int unsigned not null,
                        `actionsRevenue` double not null,
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
     */
    public function compute($clickIds)
    {

        if (!empty($clickIds)) {

            $clickIds = implode(',', array_map(function ($v) {
                return "'{$v}'";
            }, $clickIds));

            $tableName = $this->getName();
            $actionsTableName = (new TableActions)->getName();

            $sql = "insert into {$tableName} (clickId, actionsCount, actionsRevenue)
                        select
                        clickId,
                        count(clickId) as actionsCount,
                        sum(actionRevenue) as actionsRevenue
                    from {$actionsTableName}
                    where clickId in ({$clickIds})
                    group by clickId
                    on duplicate key update 
                        actionsCount = values(actionsCount),
                        actionsRevenue = values(actionsRevenue)";

            global $wpdb;
            $wpdb->query($sql);
        }
    }

    /**
     * @param \ClickerVolt\ActionSummary[] $actionSummaries
     * @throws \Exception
     */
    public function insert($actionSummaries)
    {

        $mapper = [
            'clickId' => ['type' => '%s'],
            'actionsCount' => ['type' => '%d'],
            'actionsRevenue' => ['type' => '%f'],
        ];

        $updateKeys = [
            'actionsCount' => 'actionsCount + values(actionsCount)',
            'actionsRevenue' => 'actionsRevenue + values(actionsRevenue)'
        ];
        parent::insertBulk($actionSummaries, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }
}
