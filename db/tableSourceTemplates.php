<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';
require_once __DIR__ . '/../utils/uuid.php';

class SourceTemplate implements ArraySerializer
{

    use ArraySerializerImpl;

    private $sourceId;
    private $sourceName;

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

    private $v1Name;
    private $v2Name;
    private $v3Name;
    private $v4Name;
    private $v5Name;
    private $v6Name;
    private $v7Name;
    private $v8Name;
    private $v9Name;
    private $v10Name;

    function __construct($sourceId = null, $sourceName = '', $varValues = [], $varNames = [])
    {

        $this->sourceId = $sourceId ?: UUID::alphaNum();
        $this->sourceName = $sourceName;

        $this->v1 = (array_key_exists(0, $varValues) && $varValues[0] !== '') ? $varValues[0] : null;
        $this->v2 = (array_key_exists(1, $varValues) && $varValues[1] !== '') ? $varValues[1] : null;
        $this->v3 = (array_key_exists(2, $varValues) && $varValues[2] !== '') ? $varValues[2] : null;
        $this->v4 = (array_key_exists(3, $varValues) && $varValues[3] !== '') ? $varValues[3] : null;
        $this->v5 = (array_key_exists(4, $varValues) && $varValues[4] !== '') ? $varValues[4] : null;
        $this->v6 = (array_key_exists(5, $varValues) && $varValues[5] !== '') ? $varValues[5] : null;
        $this->v7 = (array_key_exists(6, $varValues) && $varValues[6] !== '') ? $varValues[6] : null;
        $this->v8 = (array_key_exists(7, $varValues) && $varValues[7] !== '') ? $varValues[7] : null;
        $this->v9 = (array_key_exists(8, $varValues) && $varValues[8] !== '') ? $varValues[8] : null;
        $this->v10 = (array_key_exists(9, $varValues) && $varValues[9] !== '') ? $varValues[9] : null;

        $this->v1Name = (array_key_exists(0, $varNames) && $varNames[0] !== '') ? $varNames[0] : null;
        $this->v2Name = (array_key_exists(1, $varNames) && $varNames[1] !== '') ? $varNames[1] : null;
        $this->v3Name = (array_key_exists(2, $varNames) && $varNames[2] !== '') ? $varNames[2] : null;
        $this->v4Name = (array_key_exists(3, $varNames) && $varNames[3] !== '') ? $varNames[3] : null;
        $this->v5Name = (array_key_exists(4, $varNames) && $varNames[4] !== '') ? $varNames[4] : null;
        $this->v6Name = (array_key_exists(5, $varNames) && $varNames[5] !== '') ? $varNames[5] : null;
        $this->v7Name = (array_key_exists(6, $varNames) && $varNames[6] !== '') ? $varNames[6] : null;
        $this->v8Name = (array_key_exists(7, $varNames) && $varNames[7] !== '') ? $varNames[7] : null;
        $this->v9Name = (array_key_exists(8, $varNames) && $varNames[8] !== '') ? $varNames[8] : null;
        $this->v10Name = (array_key_exists(9, $varNames) && $varNames[9] !== '') ? $varNames[9] : null;
    }

    function getSourceId()
    {
        return $this->sourceId;
    }

    function getSourceName()
    {
        return $this->sourceName;
    }
}

class TableSourceTemplates extends Table
{

    const EMPTY_VAR_PLACE_HOLDER = "[%s-REPLACE-ME]";   // Where '%s' is the variable name

    const MAX_LENGTH_SOURCE_NAME = 40;
    const MAX_LENGTH_VAR_VALUES = 40;
    const MAX_LENGTH_VAR_NAMES = 40;

    /**
     * Determines if the specified var is a placeholder one...
     */
    static function isPlaceHolderVar($var)
    {

        $parts = explode('%s', self::EMPTY_VAR_PLACE_HOLDER);
        $suffix = $parts[1];

        return substr($var, -strlen($suffix)) === $suffix;
    }

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_source_templates');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {

        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $maxSourceName = self::MAX_LENGTH_SOURCE_NAME;
            $maxVarValues = self::MAX_LENGTH_VAR_VALUES;
            $maxVarNames = self::MAX_LENGTH_VAR_NAMES;

            $sql = "CREATE TABLE {$tableName} (
                        `sourceId` char(16) not null,
                        `sourceName` varchar({$maxSourceName}) not null,
                        `v1` varchar({$maxVarValues}) null,
                        `v2` varchar({$maxVarValues}) null,
                        `v3` varchar({$maxVarValues}) null,
                        `v4` varchar({$maxVarValues}) null,
                        `v5` varchar({$maxVarValues}) null,
                        `v6` varchar({$maxVarValues}) null,
                        `v7` varchar({$maxVarValues}) null,
                        `v8` varchar({$maxVarValues}) null,
                        `v9` varchar({$maxVarValues}) null,
                        `v10` varchar({$maxVarValues}) null,
                        `v1Name` varchar({$maxVarNames}) null,
                        `v2Name` varchar({$maxVarNames}) null,
                        `v3Name` varchar({$maxVarNames}) null,
                        `v4Name` varchar({$maxVarNames}) null,
                        `v5Name` varchar({$maxVarNames}) null,
                        `v6Name` varchar({$maxVarNames}) null,
                        `v7Name` varchar({$maxVarNames}) null,
                        `v8Name` varchar({$maxVarNames}) null,
                        `v9Name` varchar({$maxVarNames}) null,
                        `v10Name` varchar({$maxVarNames}) null,
                        PRIMARY KEY (`sourceId`),
                        UNIQUE KEY `sourceName_idx` (`sourceName`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * 
     * @return array
     */
    public function loadIdsToNames()
    {

        $namesToIds = [];

        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results("select sourceId, sourceName from {$tableName}", ARRAY_A);

        foreach ($rows as $row) {
            $namesToIds[$row['sourceId']] = $row['sourceName'];
        }

        return $namesToIds;
    }

    /**
     * 
     * @return ClickerVolt\SourceTemplate[]
     */
    public function loadAll()
    {

        global $wpdb;

        $tableName = $this->getName();
        $rows = $wpdb->get_results("select * from {$tableName}", ARRAY_A);

        $templates = [];
        foreach ($rows as $row) {
            $templates[] = $this->rowToSourceTemplate($row);
        }

        return $templates;
    }

    /**
     * 
     */
    private function rowToSourceTemplate($row)
    {

        $template = new SourceTemplate();
        return $template->fromArray($row);
    }

    /**
     * 
     */
    public function delete($sourceId)
    {

        global $wpdb;
        if (false === $wpdb->delete($this->getName(), ['sourceId' => $sourceId], ['%s'])) {
            throw new \Exception($wpdb->last_error);
        }
    }

    /**
     * @param \ClickerVolt\SourceTemplate[] $templates
     * @throws \Exception
     */
    public function insert($templates)
    {

        DB::singleton()->transactionStart();
        try {

            foreach ($templates as $template) {
                $this->delete($template->getSourceId());
            }

            $mapper = [
                'sourceId' => ['type' => '%s'],
                'sourceName' => ['type' => '%s', 'filter' => function ($data) {
                    return substr($data, 0, self::MAX_LENGTH_SOURCE_NAME);
                }],
                'v1' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v2' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v3' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v4' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v5' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v6' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v7' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v8' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v9' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v10' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_VALUES);
                }],
                'v1Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v2Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v3Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v4Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v5Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v6Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v7Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v8Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v9Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
                'v10Name' => ['type' => '%s', 'filter' => function ($data) {
                    return $data === null ? self::NULL_TOKEN : substr($data, 0, self::MAX_LENGTH_VAR_NAMES);
                }],
            ];

            $updateKeys = [
                'sourceName' => 'values(sourceName)',
                'v1' => 'values(v1)',
                'v2' => 'values(v2)',
                'v3' => 'values(v3)',
                'v4' => 'values(v4)',
                'v5' => 'values(v5)',
                'v6' => 'values(v6)',
                'v7' => 'values(v7)',
                'v8' => 'values(v8)',
                'v9' => 'values(v9)',
                'v10' => 'values(v10)',
                'v1Name' => 'values(v1Name)',
                'v2Name' => 'values(v2Name)',
                'v3Name' => 'values(v3Name)',
                'v4Name' => 'values(v4Name)',
                'v5Name' => 'values(v5Name)',
                'v6Name' => 'values(v6Name)',
                'v7Name' => 'values(v7Name)',
                'v8Name' => 'values(v8Name)',
                'v9Name' => 'values(v9Name)',
                'v10Name' => 'values(v10Name)',
            ];

            parent::insertBulk($templates, $mapper, ['onDuplicateKeyUpdate' => $updateKeys]);

            DB::singleton()->transactionCommit();
        } catch (\Exception $ex) {

            DB::singleton()->transactionRollback();
            throw $ex;
        }
    }
}
