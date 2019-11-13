<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStats.php';
require_once __DIR__ . '/tableClicks.php';

abstract class TableStatsWholePathVarX extends TableStatsWholePath
{

    abstract public function getVarNumber();

    public function getName()
    {
        $x = $this->getVarNumber();
        return $this->wpTableName("clickervolt_stats_whole_path_var{$x}");
    }

    /**
     * 
     * @return array 
     * [
     *   "columnName" => "mysql type definition",
     *   "columnName" => "mysql type definition",
     *   etc...
     * ]
     */
    public function getColumns()
    {

        $x = $this->getVarNumber();
        $maxVarLength = TableClicks::MAX_VAR_LENGTH;

        return array_merge(parent::getColumns(), [
            "v{$x}" => "varchar({$maxVarLength}) not null",
        ]);
    }

    /**
     * 
     */
    public function getInsertMapper()
    {

        $x = $this->getVarNumber();

        return array_merge(parent::getInsertMapper(), [
            "v{$x}" => ['type' => '%s', 'filter' => function ($var) {
                $var = TableClicks::filterVariableForInsert($var);
                if ($var === self::NULL_TOKEN) {
                    $var = self::NONE;
                }
                return $var;
            }],
        ]);
    }
}
