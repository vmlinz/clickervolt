<?php

namespace ClickerVolt;

require_once __DIR__ . '/../utils/uuid.php';
require_once __DIR__ . '/../utils/fileTools.php';
require_once __DIR__ . '/table.php';
require_once __DIR__ . '/tableDevices.php';
require_once __DIR__ . '/tableGeos.php';
require_once __DIR__ . '/tableReferrers.php';
require_once __DIR__ . '/tableSourceTemplates.php';

class Click implements ArraySerializer
{
    use ArraySerializerImpl;

    const STORAGE_PATH = 'unprocessed_clicks';

    const COL_V1 = 'v1';
    const COL_V2 = 'v2';
    const COL_V3 = 'v3';
    const COL_V4 = 'v4';
    const COL_V5 = 'v5';
    const COL_V6 = 'v6';
    const COL_V7 = 'v7';
    const COL_V8 = 'v8';
    const COL_V9 = 'v9';
    const COL_V10 = 'v10';

    static $colVars = [
        self::COL_V1,
        self::COL_V2,
        self::COL_V3,
        self::COL_V4,
        self::COL_V5,
        self::COL_V6,
        self::COL_V7,
        self::COL_V8,
        self::COL_V9,
        self::COL_V10,
    ];

    // Properties that have a direct mapping in db's clicks table
    private $id;
    private $linkId;
    private $linkIdsPath;
    private $source;
    private $url;
    private $urlsPathHash;
    private $timestamp;
    private $ip;
    private $geoHash;
    private $deviceHash;
    private $referrerHash;
    private $isUnique;
    private $v1;
    private $v2;
    private $v3;
    private $v4;
    private $v5;
    private $v6;
    private $v7;
    private $v8;
    private $v9;
    private $v10;

    // Properties needed for some processing, but that are not stored in db as part of the click
    private $userAgent;
    private $language;
    private $referrer;
    private $geo;
    private $urlsPath;

    /**
     * 
     */
    function __construct($array = [])
    {

        $this->fromArray($array);

        if (empty($array['id'])) {
            $this->id = UUID::alphaNum();
        }
    }

    /**
     * 
     */
    function queue()
    {

        $toQueue = json_encode($this->toArray());

        $file = implode('_', [
            $this->id,
        ]);

        $path = FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$file}";
        FileTools::atomicSave($path, $toQueue);
    }

    function getClickId()
    {
        return $this->id;
    }

    function getLinkId()
    {
        return $this->linkId;
    }

    function getReferrer()
    {
        return $this->referrer;
    }

    function getGeoHash()
    {
        return $this->geoHash;
    }

    function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return Device
     */
    function resolveDeviceData()
    {

        $device = new Device($this->userAgent, $this->language);
        $device->resolveDeviceData();
        $this->deviceHash = $device->getDeviceHash();
        return $device;
    }

    /**
     * @return Geo
     */
    function resolveGeoIP()
    {

        if (!$this->geo) {

            $this->geo = new Geo($this->ip);
            $this->geo->resolveGeoIP();
            $this->geoHash = $this->geo->getGeoHash();
        } else if (is_array($this->geo)) {

            $geoObj = new Geo($this->ip);
            $geoObj->fromArray($this->geo);
            $this->geo = $geoObj;
        }
        return $this->geo;
    }

    /**
     * @return Referrer
     */
    function resolveReferrer()
    {

        $ref = new Referrer($this->referrer);
        $this->referrerHash = $ref->getReferrerHash();
        return $ref;
    }
}

class TableClicks extends Table
{

    const MAX_VAR_LENGTH = 100;

    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_clicks');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {

        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $maxVLength = self::MAX_VAR_LENGTH;
            $nbPartitions = 16;
            $partitionClause = "PARTITION BY HASH(linkId % {$nbPartitions}) PARTITIONS {$nbPartitions}";

            $sql = "CREATE TABLE {$tableName} (
                        `id` char(16) not null,
                        `linkId` int unsigned not null,
                        `linkIdsPath` varchar(255) not null,
                        `source` varchar(40) not null,
                        `url` varchar(255) not null,
                        `urlsPathHash` binary(16) not null,
                        `timestamp` int unsigned not null,
                        `ip` varbinary(16) not null,
                        `geoHash` binary(16) null,
                        `deviceHash` binary(16) null,
                        `referrerHash` binary(16) null,
                        `isUnique` tinyint unsigned not null,
                        `v1` varchar({$maxVLength}) null,
                        `v2` varchar({$maxVLength}) null,
                        `v3` varchar({$maxVLength}) null,
                        `v4` varchar({$maxVLength}) null,
                        `v5` varchar({$maxVLength}) null,
                        `v6` varchar({$maxVLength}) null,
                        `v7` varchar({$maxVLength}) null,
                        `v8` varchar({$maxVLength}) null,
                        `v9` varchar({$maxVLength}) null,
                        `v10` varchar({$maxVLength}) null,
                        primary key (`id`, `linkId`),
                        key `paths_idx` (`linkId`, `linkIdsPath`, `urlsPathHash`),
                        key `whole_path_idx` (`linkId`, `source`, `url`, `linkIdsPath`, `urlsPathHash`),
                        key `timestamp_idx` (`timestamp`),
                        key `geoHash_idx` (`geoHash`),
                        key `deviceHash_idx` (`deviceHash`),
                        key `referrerHash_idx` (`referrerHash`),
                        key `isUnique_idx` (`isUnique`),
                        key `v1_idx` (`v1`),
                        key `v2_idx` (`v2`),
                        key `v3_idx` (`v3`),
                        key `v4_idx` (`v4`),
                        key `v5_idx` (`v5`),
                        key `v6_idx` (`v6`),
                        key `v7_idx` (`v7`),
                        key `v8_idx` (`v8`),
                        key `v9_idx` (`v9`),
                        key `v10_idx` (`v10`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query("{$sql} {$partitionClause}");
            if ($res === false) {
                $res = $wpdb->query($sql);
            }
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * 
     * @param string $id
     * @return \ClickerVolt\Click or null if not found
     */
    public function load($id)
    {

        $clicks = $this->loadAll([$id]);
        return empty($clicks) ? null : $clicks[$id];
    }

    /**
     * 
     * @param array $ids
     * @return array
     */
    public function loadAll($ids)
    {

        global $wpdb;

        $clicks = [];

        if ($ids) {

            $format = implode(',', array_fill(0, count($ids), '%s'));

            $tableName = $this->getName();
            $rows = $wpdb->get_results(
                $wpdb->prepare("select * from {$tableName} where id in ({$format})", $ids),
                ARRAY_A
            );

            foreach ($rows as $row) {
                $click = $this->rowToClick($row);
                $clicks[$click->getClickId()] = $click;
            }
        }

        return $clicks;
    }

    /**
     * 
     */
    private function rowToClick($row)
    {

        $click = new Click();

        $row['ip'] = $row['ip'] ? inet_ntop($row['ip']) : null;
        $row['geoHash'] = $row['geoHash'] ? bin2hex($row['geoHash']) : null;
        $row['deviceHash'] = $row['deviceHash'] ? bin2hex($row['deviceHash']) : null;
        $row['referrerHash'] = $row['referrerHash'] ? bin2hex($row['referrerHash']) : null;
        $row['urlsPathHash'] = $row['urlsPathHash'] ? bin2hex($row['urlsPathHash']) : null;

        return $click->fromArray($row);
    }

    /**
     * @param \ClickerVolt\Click[] $clicks
     * @throws \Exception
     */
    public function insert($clicks)
    {

        $mapper = [
            'id' => ['type' => '%s'],
            'linkId' => ['type' => '%d'],
            'linkIdsPath' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 255) : '';
            }],
            'source' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, 40);
            }],
            'url' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, 255);
            }],
            'urlsPathHash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'timestamp' => ['type' => '%d'],
            'ip' => ['type' => '%s', 'filter' => function ($data) {
                $ip = @inet_pton($data);
                if (!$ip) {
                    $ip = null;
                }
                return $ip;
            }],
            'geoHash' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? hex2bin($data) : self::NULL_TOKEN;
            }],
            'deviceHash' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? hex2bin($data) : self::NULL_TOKEN;
            }],
            'referrerHash' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? hex2bin($data) : self::NULL_TOKEN;
            }],
            'isUnique' => ['type' => '%d', 'filter' => function ($data) {
                return $data ? 1 : 0;
            }],
            'v1' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v2' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v3' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v4' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v5' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v6' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v7' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v8' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v9' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
            'v10' => ['type' => '%s', 'filter' => ['ClickerVolt\\TableClicks', 'filterVariableForInsert']],
        ];

        parent::insertBulk($clicks, $mapper, ['insertModifiers' => ['ignore']]);
    }

    /**
     * 
     */
    static function filterVariableForInsert($var)
    {

        if (TableSourceTemplates::isPlaceHolderVar($var)) {
            $var = null;
        }

        if ($var === null) {
            $var = self::NULL_TOKEN;
        } else {
            $var = substr($var, 0, self::MAX_VAR_LENGTH);
        }

        return $var;
    }
}
