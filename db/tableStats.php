<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';

class StatsRow implements ArraySerializer
{

    use ArraySerializerImpl;

    private $timestampHour;
    private $clicks;
    private $clicksUnique;
    private $revenue;
    private $actions;
    private $hasAttention;
    private $hasInterest;
    private $hasDesire;
    private $suspiciousScoreSum;

    /**
     * 
     */
    static function calculateTimestampHour($fromTimestamp)
    {
        return $fromTimestamp - ($fromTimestamp % 3600);
    }

    /**
     * 
     */
    function __construct($timestamp, $additionalData = [])
    {

        $this->timestampHour = self::calculateTimestampHour($timestamp);

        $this->clicks = 0;
        $this->clicksUnique = 0;
        $this->revenue = 0;
        $this->actions = 0;
        $this->hasAttention = 0;
        $this->hasInterest = 0;
        $this->hasDesire = 0;
        $this->suspiciousScoreSum = 0;

        $this->fromArray($additionalData);
    }

    function addClicks($addValue)
    {
        $this->clicks += $addValue;
    }

    function addClicksUnique($addValue)
    {
        $this->clicksUnique += $addValue;
    }

    function addAttention($addValue)
    {
        $this->hasAttention += $addValue;
    }

    function addInterest($addValue)
    {
        $this->hasInterest += $addValue;
    }

    function addDesire($addValue)
    {
        $this->hasDesire += $addValue;
    }

    function addActions($addValue)
    {
        $this->actions += $addValue;
    }

    function addRevenue($addValue)
    {
        $this->revenue += $addValue;
    }

    function addSuspiciousScore($score)
    {
        $this->suspiciousScoreSum += $score;
    }
}

abstract class TableStats extends Table
{

    const UNKNOWN = '[unknown]';
    const NONE = '[none]';

    /**
     * 
     * @return array 
     * [
     *   "columnName" => "mysql type definition",
     *   "columnName" => "mysql type definition",
     *   etc...
     * ]
     */
    abstract public function getColumns();

    /**
     * See Table::insertBulk() for description
     */
    abstract public function getInsertMapper();

    /**
     * 
     */
    protected function getPartitionCreationClause()
    {
        return "";
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {

        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $columnInfos = $this->getColumns();

            $wholePathIndex = ['`timestampHour`'];
            $additionalColumns = [];
            $additionalIndexes = [];
            foreach ($columnInfos as $column => $type) {
                $additionalColumns[] = "{$column} {$type}";
                // $additionalIndexes[] = "{$column}_idx (`{$column}`)";
                $wholePathIndex[] = "`{$column}`";
            }

            $additionalColumns = empty($additionalColumns) ? '' : implode(', ', $additionalColumns) . ', ';
            $additionalIndexes = empty($additionalIndexes) ? '' : ', ' . implode(', ', $additionalIndexes);
            $wholePathIndex = implode(', ', $wholePathIndex);
            $partitionClause = $this->getPartitionCreationClause();

            // We cannot have `suspiciousScoreSum` as unsigned because in our 'on duplicate key update' inserts, 
            // we sometimes add negative values for adjustment. 
            // Mysql's behavior is to set all negative values to 0 BEFORE it updates the column,
            // even when the result of the operation is >= 0 
            // Documented here: https://stackoverflow.com/questions/37126650/on-duplicate-key-update-decrement-value-in-mysql
            $sql = "CREATE TABLE {$tableName} (
                        `timestampHour` int unsigned,
                        {$additionalColumns}
                        `clicks` int unsigned not null default 0,
                        `clicksUnique` int unsigned not null default 0,
                        `revenue` double not null default 0,
                        `actions` int unsigned not null default 0,
                        `hasAttention` int unsigned not null default 0,
                        `hasInterest` int unsigned not null default 0,
                        `hasDesire` int unsigned not null default 0,
                        `suspiciousScoreSum` int not null default 0,
                        primary key ({$wholePathIndex}),
                        key `timestamp_idx` (`timestampHour`)
                        {$additionalIndexes}
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query("{$sql} {$partitionClause}");
            if ($res === false) {
                $res = $wpdb->query($sql);
            }
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) {

            if ($oldVersion < 1.094 && $this->doesColumnExist('qualitySamples')) {

                $sql = "ALTER TABLE {$tableName}
                        DROP COLUMN qualitySamples,
                        DROP COLUMN qualityScoreSum";

                $res = $wpdb->query($sql);
                if ($res === false) {
                    throw new \Exception("Cannot update table {$tableName}: {$wpdb->last_error}");
                }
            }

            if ($oldVersion < 1.095) {

                if (!$this->doesColumnExist('suspiciousScoreSum')) {
                    $sql = "ALTER TABLE {$tableName}
                            ADD suspiciousScoreSum int not null default 0";
                } else {
                    $sql = "ALTER TABLE {$tableName}
                            MODIFY suspiciousScoreSum int not null default 0";
                }

                $res = $wpdb->query($sql);
                if ($res === false) {
                    throw new \Exception("Cannot update table {$tableName}: {$wpdb->last_error}");
                }
            }
        }
    }

    /**
     * @param StatsRow[] $statsRows
     * @throws \Exception
     */
    public function insert($statsRows)
    {

        $mapper = [
            'timestampHour' => ['type' => '%d'],
            'clicks' => ['type' => '%d'],
            'clicksUnique' => ['type' => '%d'],
            'revenue' => ['type' => '%f'],
            'actions' => ['type' => '%d'],
            'hasAttention' => ['type' => '%d'],
            'hasInterest' => ['type' => '%d'],
            'hasDesire' => ['type' => '%d'],
            'suspiciousScoreSum' => ['type' => '%d'],
        ];

        $mapper = array_merge($mapper, $this->getInsertMapper());

        $updateKeys = [
            '`clicks`' => '`clicks` + values(`clicks`)',
            '`clicksUnique`' => '`clicksUnique` + values(`clicksUnique`)',
            '`revenue`' => '`revenue` + values(`revenue`)',
            '`actions`' => '`actions` + values(`actions`)',
            '`hasAttention`' => '`hasAttention` + values(`hasAttention`)',
            '`hasInterest`' => '`hasInterest` + values(`hasInterest`)',
            '`hasDesire`' => '`hasDesire` + values(`hasDesire`)',
            '`suspiciousScoreSum`' => '`suspiciousScoreSum` + values(`suspiciousScoreSum`)',
        ];
        parent::insertBulk($statsRows, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }

    static function registerClass($className)
    {
        self::$registeredClasses[$className] = $className;
    }

    static function getRegisteredClasses()
    {
        return self::$registeredClasses;
    }

    private static $registeredClasses = [];
}
