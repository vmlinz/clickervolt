<?php

namespace ClickerVolt;

require_once __DIR__ . '/../utils/stringTools.php';

if (file_exists(__DIR__ . '/../DEBUG.php')) {
    require_once __DIR__ . '/../DEBUG.php';
}

class DB
{
    const VERSION = 1.145;

    const OPTION_VERSION = 'clickervolt-version';

    static private $tableClasses = [
        'ClickerVolt\TableLinks',
        'ClickerVolt\TableClicks',
        'ClickerVolt\TableParallelIds',
        'ClickerVolt\TableExternalIds',
        'ClickerVolt\TableFunnelLinks',
        'ClickerVolt\TableActions',
        'ClickerVolt\TableActionsSummary',
        'ClickerVolt\TableAIDs',
        'ClickerVolt\TableSuspiciousClicks',
        'ClickerVolt\TableDevices',
        'ClickerVolt\TableGeos',
        'ClickerVolt\TableReferrers',
        'ClickerVolt\TableSourceTemplates',
        'ClickerVolt\TableURLsPaths',
        'ClickerVolt\TableStatsBase',
        'ClickerVolt\TableStatsWholePath',
        'ClickerVolt\TableStatsWholePathGeos',
        'ClickerVolt\TableStatsWholePathDevices',
        'ClickerVolt\TableStatsWholePathReferrers',
        'ClickerVolt\TableStatsWholePathVar1',
        'ClickerVolt\TableStatsWholePathVar2',
        'ClickerVolt\TableStatsWholePathVar3',
        'ClickerVolt\TableStatsWholePathVar4',
        'ClickerVolt\TableStatsWholePathVar5',
        'ClickerVolt\TableStatsWholePathVar6',
        'ClickerVolt\TableStatsWholePathVar7',
        'ClickerVolt\TableStatsWholePathVar8',
        'ClickerVolt\TableStatsWholePathVar9',
        'ClickerVolt\TableStatsWholePathVar10',
    ];

    /**
     * 
     * @return \ClickerVolt\DB
     */
    static public function singleton()
    {
        if (!self::$singleton) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    /**
     * 
     */
    public function includeAll()
    {
        foreach (self::$tableClasses as $class) {
            require_once __DIR__ . '/' . StringTools::getFileNameFromClassPath($class);
        }
    }

    /**
     * @param string $newTZ - like '+0:00', or 'SYSTEM'
     * @return string previous timezone
     */
    public function setTimezone($newTZ)
    {
        global $wpdb;

        $prevTZ = null;
        $rows = $wpdb->get_results("select @@session.time_zone as tz", OBJECT);
        if ($rows) {
            $prevTZ = $rows[0]->tz;
        }

        $wpdb->query("SET time_zone = '{$newTZ}'");

        return $prevTZ;
    }

    /**
     * 
     */
    public function setupTables()
    {
        $v = get_option(DB::OPTION_VERSION);
        if (!$v || $v < DB::VERSION) {

            foreach (DB::$tableClasses as $tableClass) {
                $filename = StringTools::getFileNameFromClassPath($tableClass);
                require_once __DIR__ . "/{$filename}";

                $classInstance = new $tableClass;
                $classInstance->setup($v, DB::VERSION);
            }

            if ($v < 1.099) {
                $timestamp = wp_next_scheduled('clickervolt_cron_maxmind_update');
                if ($timestamp) {
                    wp_unschedule_event($timestamp, 'clickervolt_cron_maxmind_update');
                }
            }

            update_option(DB::OPTION_VERSION, DB::VERSION);
        }
    }

    public function transactionStart()
    {
        global $wpdb;
        $this->curTransactions++;
        if ($this->curTransactions == 1) {
            $wpdb->query('start transaction');
        }
    }

    public function transactionCommit()
    {
        global $wpdb;
        if ($this->curTransactions > 0) {
            $this->curTransactions--;
            if ($this->curTransactions == 0) {
                $wpdb->query('commit');
            }
        }
    }

    public function transactionRollback()
    {
        global $wpdb;
        if ($this->curTransactions > 0) {
            $this->curTransactions--;
            if ($this->curTransactions == 0) {
                $wpdb->query('rollback');
            }
        }
    }

    private function __construct()
    {
        $this->curTransactions = 0;
    }

    static private $singleton = null;
    private $curTransactions;
}
