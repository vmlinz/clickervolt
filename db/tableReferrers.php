<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';

class Referrer implements ArraySerializer
{

    use ArraySerializerImpl;

    private $referrerHash;
    private $referrer;

    function __construct($referrer)
    {
        $this->referrer = $referrer;
        $this->referrerHash = md5($referrer);
    }

    function getReferrerHash()
    {
        return $this->referrerHash;
    }
}

class TableReferrers extends Table
{

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_referrers');
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
                        `referrerHash` binary(16) not null,
                        `referrer` varchar(255) not null,
                        PRIMARY KEY (`referrerHash`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param \ClickerVolt\Referrer[] $referrers
     * @throws \Exception
     */
    public function insert($referrers)
    {

        $mapper = [
            'referrerHash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'referrer' => ['type' => '%s', 'filter' => function ($data) {
                return substr($data, 0, 255);
            }],
        ];

        parent::insertBulk($referrers, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
