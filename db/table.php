<?php

namespace ClickerVolt;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../utils/arraySerializer.php';

abstract class Table
{
    abstract public function getName();
    abstract public function setup($oldVersion, $newVersion);

    /**
     * Returns the table name with Wordpress's prefix added if not there yet
     * 
     * @param string $tableName
     * @return string
     */
    protected function wpTableName($tableName)
    {
        global $wpdb;

        if (strpos($tableName, $wpdb->prefix) !== 0) {
            $tableName = $wpdb->prefix . $tableName;
        }

        return $tableName;
    }

    /**
     * 
     * @return boolean
     */
    function doesTableExist()
    {
        global $wpdb;

        try {
            $tableName = $this->wpTableName($this->getName());
            $rows = $wpdb->get_results("show tables like '{$tableName}'", OBJECT);
            return $rows && is_array($rows) && (count($rows) == 1);
        } catch (\Exception $ex) { }

        return false;
    }

    /**
     * 
     * @return boolean
     */
    function doesColumnExist($columnName)
    {
        global $wpdb;

        try {
            $wpdb->suppress_errors(true);
            $tableName = $this->wpTableName($this->getName());
            $result = $wpdb->query("select `{$columnName}` from {$tableName} limit 1");
            return $result !== false && empty($wpdb->last_error) ? true : false;
        } catch (\Exception $ex) { } finally {
            $wpdb->suppress_errors(false);
        }

        return false;
    }

    /**
     * 
     * @param \ClickerVolt\ArraySerializer[] $entries
     * @param array $columnsDataMapper - an array mapping the column names to their type, optional data filters and acceptation callback
     * 
     * Example:
     * 
     *  $columnsDataMapper = [
     *      'deviceHash' => [ 'type' => '%s', 'filter' => function($data) { return $data; }, 'accept' => function($data) { return $data !== null; } ],
     *      'deviceType' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceBrand' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceName' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceOs' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceOsVersion' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceBrowser' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceBrowserVersion' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 40) : self::NULL_TOKEN; } ],
     *      'deviceLanguage' => [ 'type' => '%s', 'filter' => function($data) { return $data ? substr($data, 0, 5) : null; } ],
     *  ];
     * 
     * The $data received by the filter is the data retrieved from the passed entries. The key names in these entries MUST match the DB column names
     * 
     * @param array $options
     * 
     * @throws \Exception
     */
    protected function insertBulk($entries, $columnsDataMapper, $options = [])
    {
        global $wpdb;

        if (!empty($entries) && $columnsDataMapper) {

            $defaultOptions = [
                'insertModifiers' => [],
                'onDuplicateKeyUpdate' => [],       // Array of key-value pairs for columns to updates (set as the keys) with their values
            ];

            $options = array_merge($defaultOptions, $options);

            $columns = implode(',', array_keys($columnsDataMapper));
            $types = implode(',', array_map(function ($v) {
                return $v['type'];
            }, $columnsDataMapper));
            $types = "({$types})";

            $nullToken = self::NULL_TOKEN;

            $values = [];
            foreach ($entries as $entry) {

                $args = [];
                $args[] = $types;
                $accept = true;

                $entryData = $entry->toArray();
                foreach ($columnsDataMapper as $k => $info) {

                    if (array_key_exists($k, $entryData)) {

                        $data = $entryData[$k];
                        if (!empty($info['accept'])) {
                            $accept = call_user_func($info['accept'], $data);
                            if (!$accept) {
                                break;
                            }
                        }
                        if (!empty($info['filter'])) {
                            $data = call_user_func($info['filter'], $data);
                        }
                        $args[] = $data;
                    } else {

                        $args[] = $nullToken;
                    }
                }

                if ($accept) {
                    $values[] = str_replace("'{$nullToken}'", 'null', call_user_func_array([$wpdb, 'prepare'], $args));
                }
            }

            $tableName = $this->getName();
            $insertModifiers = implode(' ', $options['insertModifiers']);

            $sql = "insert {$insertModifiers} into {$tableName} ({$columns}) values ";
            $sql .= implode(',', $values);

            if (!empty($options['onDuplicateKeyUpdate'])) {

                $updateClauses = [];
                foreach ($options['onDuplicateKeyUpdate'] as $k => $v) {

                    $updateClauses[] = "{$k} = {$v}";
                }

                $sql .= ' on duplicate key update ' . implode(', ', $updateClauses);
            }

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot insert entries into {$tableName}: {$wpdb->last_error}");
            }
        }
    }

    const NULL_TOKEN = '##null##';
}
