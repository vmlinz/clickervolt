<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';

class URLsPath implements ArraySerializer
{

    use ArraySerializerImpl;

    const SEPARATOR = '>$>';

    private $hash;
    private $path;

    /**
     * @param array $urls
     */
    function __construct($urls)
    {

        if (!is_array($urls)) {
            throw new \Exception("urls must be passed as an array");
        }

        $this->path = implode(self::SEPARATOR, $urls);
        $this->hash = md5($this->path);
    }

    function getHash()
    {
        return $this->hash;
    }
}

class TableURLsPaths extends Table
{

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_urls_paths');
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
                        `hash` binary(16) not null,
                        `path` text not null,
                        primary key (`hash`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param \ClickerVolt\URLsPath[] $urlsPaths
     * @throws \Exception
     */
    public function insert($urlsPaths)
    {

        $mapper = [
            'hash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'path' => ['type' => '%s'],
        ];

        parent::insertBulk($urlsPaths, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
