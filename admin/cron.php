<?php

namespace ClickerVolt;

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/objects/maybeSuspiciousClick.php';
require_once __DIR__ . '/../utils/fileTools.php';
require_once __DIR__ . '/../utils/urlTools.php';
require_once __DIR__ . '/../utils/singleProcess.php';
require_once __DIR__ . '/setup.php';

class Cron
{
    /**
     * 
     */
    static function getClicksQueueBatchSize()
    {
        return 1000;
        // return is_admin() ? 1000 : 10;
    }

    /**
     * 
     * @throws \Exception
     */
    static function processClicksQueue()
    {
        $sp = new SingleProcess('processClicksQueue');
        $sp->executeIfNone(function () {

            require_wp_db();
            DB::singleton()->includeAll();

            $clicks = [];
            $parallelIds = [];
            $externalIds = [];
            $aids = [];
            $suspiciousClicks = [];
            $actions = [];
            $clickIdToActions = [];
            $oldClicksReloaded = [];
            $devices = [];
            $geos = [];
            $referrers = [];
            $urlsPaths = [];
            $filesToDelete = [];

            $tableClicks = new TableClicks();
            $tableParallelIds = new TableParallelIds();
            $tableLinks = new TableLinks();

            $folder = FileTools::getDataFolderPath(Click::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {

                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {

                    $fullPath = $folder . $file;
                    $clickContent = FileTools::atomicLoad($fullPath);
                    if ($clickContent) {

                        $data = json_decode($clickContent, true);
                        if ($data) {

                            $urlsPath = new URLsPath($data['urlsPath']);
                            $urlsPaths[$urlsPath->getHash()] = $urlsPath;
                            $data['urlsPathHash'] = $urlsPath->getHash();

                            if (!empty($data['organic'])) {
                                // For organic traffic, we don't want to keep query parameters on visited URL
                                // as we could potentially have millions of different URLs when unique IDs are passed
                                $params = URLTools::getParams($data['url']);
                                foreach ($params as $k => $v) {
                                    $params[$k] = '';
                                }
                                $data['url'] = URLTools::setParams($data['url'], $params);
                            }

                            $click = new Click($data);

                            $device = $click->resolveDeviceData();
                            $devices[$device->getDeviceHash()] = $device;

                            if (!empty($data['ip'])) {
                                $geo = $click->resolveGeoIP();
                                $geos[$click->getGeoHash()] = $geo;
                            }

                            if ($click->getReferrer()) {
                                $referrer = $click->resolveReferrer();
                                $referrers[$referrer->getReferrerHash()] = $referrer;
                            }

                            if (!empty($data['externalId'])) {
                                $externalIds[] = new ExternalId($click->getClickId(), $data['externalId']);
                            }

                            $clicks[$click->getClickId()] = $click;
                            $filesToDelete[] = $fullPath;
                        }
                    }
                }
            }

            $folder = FileTools::getDataFolderPath(ParallelId::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {
                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {
                    $fullPath = $folder . $file;
                    $parallelContent = FileTools::atomicLoad($fullPath);
                    if ($parallelContent) {

                        $data = json_decode($parallelContent, true);
                        if ($data && !empty($data['parallelId']) && !empty($data['clickId'])) {

                            $parallelId = new ParallelId($data);

                            if (
                                !array_key_exists('timeCreated', $data) // old file format without this key
                                || $data['timeCreated'] < (time() - ParallelId::MAX_PAIRING_DELAY) // too much time elapsed without parallel click pairing
                                || !empty($data['paired']) // paired successfully!
                            ) {
                                if (!empty($data['clickData'])) {
                                    $pairedClick = new Click($data['clickData']);
                                    $pairedClick->queue();
                                }
                                $parallelIds[$data['parallelId']] = $parallelId;
                                $filesToDelete[] = $fullPath;
                            }
                        }
                    }
                }
            }

            $folder = FileTools::getDataFolderPath(AID::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {

                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {

                    $fullPath = $folder . $file;
                    $aidContent = FileTools::atomicLoad($fullPath);
                    if ($aidContent) {

                        $data = json_decode($aidContent, true);
                        if ($data) {

                            $aid = new AID($data);
                            $aids[$aid->getClickId()] = $aid;

                            $clickId = $aid->getClickId();

                            if (!empty($clicks[$clickId])) {
                                $click = $clicks[$clickId];
                            } else {
                                $click = $tableClicks->load($clickId);
                                $oldClicksReloaded[$clickId] = $clickId;
                            }

                            if ($click) {
                                $click->fromArray($data);
                                $clicks[$clickId] = $click;
                            }

                            $filesToDelete[] = $fullPath;
                        }
                    }
                }
            }
            if (!empty($aids)) {
                // Check if we have already saved AID values for some of these click ids
                $clickIds = array_keys($aids);
                $table = new TableAIDs();
                $oldAIDs = $table->load($clickIds);
                if (!empty($oldAIDs)) {
                    foreach ($oldAIDs as $clickId => $oldAID) {
                        $oldValues = $oldAID->toArray();
                        $newValues = $aids[$clickId]->toArray();

                        // New AID triggers only if not triggered previously
                        if (intval($oldValues['hasAttention'])) {
                            $newValues['hasAttention'] = 0;
                        }
                        if (intval($oldValues['hasInterest'])) {
                            $newValues['hasInterest'] = 0;
                        }
                        if (intval($oldValues['hasDesire'])) {
                            $newValues['hasDesire'] = 0;
                        }
                        if (!$newValues['hasAttention'] && !$newValues['hasInterest'] && !$newValues['hasDesire']) {
                            // No changes between old and new values...
                            unset($aids[$clickId]);
                        }

                        if ($clicks[$clickId]) {
                            $clicks[$clickId]->fromArray($newValues);
                        }
                    }
                }
            }

            $folder = FileTools::getDataFolderPath(MaybeSuspiciousClick::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {

                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {

                    $fullPath = $folder . $file;
                    $content = FileTools::atomicLoad($fullPath);
                    if ($content) {

                        $data = json_decode($content, true);
                        if ($data) {

                            $maybeSuspiciousClick = new MaybeSuspiciousClick(null);
                            $maybeSuspiciousClick->fromArray($data);

                            if ($maybeSuspiciousClick->getTimeCreated() < (time() - MaybeSuspiciousClick::MAX_CANCELLING_DELAY)) {
                                $suspiciousClick = new SuspiciousClick($maybeSuspiciousClick->getClickId(), 100);
                                $suspiciousClick->queue();
                                $filesToDelete[] = $fullPath;
                            }
                        }
                    }
                }
            }

            $folder = FileTools::getDataFolderPath(SuspiciousClick::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {

                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {

                    $fullPath = $folder . $file;
                    $content = FileTools::atomicLoad($fullPath);
                    if ($content) {

                        $data = json_decode($content, true);
                        if ($data) {

                            $clickId = $data['clickId'];

                            $cq = new SuspiciousClick($clickId, $data['score']);
                            $suspiciousClicks[$clickId] = $cq;

                            if (!empty($clicks[$clickId])) {
                                $click = $clicks[$clickId];
                            } else {
                                $click = $tableClicks->load($clickId);
                                $oldClicksReloaded[$clickId] = $clickId;
                            }

                            if ($click) {
                                $click->fromArray(['suspiciousScore' => $data['score']]);
                                $clicks[$clickId] = $click;
                            }

                            $filesToDelete[] = $fullPath;
                        }
                    }
                }
            }
            if (!empty($suspiciousClicks)) {
                // Check if we have already saved suspicious scores for some of these click ids
                $clickIds = array_keys($suspiciousClicks);
                $table = new TableSuspiciousClicks();
                $oldSuspiciousClicks = $table->load($clickIds);
                if (!empty($oldSuspiciousClicks)) {
                    foreach ($oldSuspiciousClicks as $clickId => $oldSC) {
                        $oldValues = $oldSC->toArray();
                        $newValues = $suspiciousClicks[$clickId]->toArray();

                        // New score saved only if different (because of some post-processing adjustments for example)
                        if ($newValues['score'] == $oldValues['score']) {
                            unset($suspiciousClicks[$clickId]);
                            if ($clicks[$clickId]) {
                                $clicks[$clickId]->unsetProp('suspiciousScore');
                            }
                        } else {
                            if ($clicks[$clickId]) {
                                $clicks[$clickId]->fromArray(['suspiciousScore' => ($newValues['score'] - $oldValues['score'])]);
                            }
                        }
                    }
                }
            }

            $folder = FileTools::getDataFolderPath(Action::STORAGE_PATH) . DIRECTORY_SEPARATOR;
            $files = scandir($folder);
            foreach ($files as $iFile => $file) {

                if ($iFile > self::getClicksQueueBatchSize()) {
                    break;
                }

                if ($file != '.' && $file != '..') {

                    $fullPath = $folder . $file;
                    $actionContent = FileTools::atomicLoad($fullPath);
                    if ($actionContent) {

                        $data = json_decode($actionContent, true);
                        if ($data) {

                            $clickId = $data['clickId'];
                            $actionType = $data['actionType'];
                            $actionName = $data['actionName'];
                            $revenue = $data['actionRevenue'];
                            $clickTimestamp = empty($data['clickTimestamp']) ? null : $data['clickTimestamp'];
                            $actionTimestamp = empty($data['actionTimestamp']) ? null : $data['actionTimestamp'];
                            $restrictToSlug = isset($data['restrictToSlug']) ? $data['restrictToSlug'] : '';

                            if (!empty($clicks[$clickId])) {
                                $click = $clicks[$clickId];
                            } else {
                                $click = $tableClicks->load($clickId);
                                if (!$click) {
                                    // Click id not found... would it be a parallel click?
                                    if (!empty($parallelIds[$clickId])) {
                                        $parallelId = $parallelIds[$clickId];
                                    } else {
                                        $parallelId = $tableParallelIds->load($clickId);
                                    }

                                    if (!empty($parallelId)) {
                                        $clickId = $parallelId->getClickId();
                                        if (!empty($clicks[$clickId])) {
                                            $click = $clicks[$clickId];
                                        } else {
                                            $click = $tableClicks->load($clickId);
                                        }
                                    }
                                }

                                $oldClicksReloaded[$clickId] = $clickId;
                            }

                            if ($click) {
                                if ($restrictToSlug) {
                                    $link = $tableLinks->loadById($click->getLinkId());
                                    if (!$link || strtolower($restrictToSlug) != strtolower($link->getSlug())) {
                                        $click = null;
                                        $filesToDelete[] = $fullPath;
                                    }
                                }

                                if ($click) {
                                    $action = new Action([
                                        'clickId' => $clickId,
                                        'actionType' => $actionType,
                                        'actionName' => $actionName,
                                        'actionRevenue' => $revenue,
                                        'clickTimestamp' => $clickTimestamp,
                                        'actionTimestamp' => $actionTimestamp,
                                    ]);

                                    $clicks[$clickId] = $click;

                                    if (!$action->getClickTimestamp()) {
                                        $action->setClickTimestamp($click->getTimestamp());
                                    }

                                    if (empty($clickIdToActions[$clickId])) {
                                        $clickIdToActions[$clickId] = [];
                                    }
                                    $clickIdToActions[$clickId][] = $action;
                                    $actions[] = $action;
                                }
                            }

                            $filesToDelete[] = $fullPath;
                        }
                    }
                }
            }


            if ($clicks || $parallelIds || $aids || $actions) {

                DB::singleton()->transactionStart();
                try {

                    $table = new TableClicks();
                    $table->insert($clicks);

                    $table = new TableParallelIds();
                    $table->insert($parallelIds);

                    $table = new TableExternalIds();
                    $table->insert($externalIds);

                    $table = new TableURLsPaths();
                    $table->insert($urlsPaths);

                    $table = new TableAIDs();
                    $table->insert($aids);

                    $table = new TableSuspiciousClicks();
                    $table->insert($suspiciousClicks);

                    $table = new TableActions();
                    $table->insert($actions);

                    $table = new TableActionsSummary();
                    $table->compute(array_keys($clickIdToActions));

                    $table = new TableDevices();
                    $table->insert($devices);

                    $table = new TableReferrers();
                    $table->insert($referrers);

                    $table = new TableGeos();
                    $table->insert($geos);

                    $statsTableNames = TableStats::getRegisteredClasses();
                    foreach ($statsTableNames as $statsTableName) {

                        $statsTable = new $statsTableName;
                        $columns = array_keys($statsTable->getColumns());

                        $statsRows = [];
                        foreach ($clicks as $click) {

                            $clickData = $click->toArray();

                            $columnsData = [];

                            foreach ($columns as $colName) {
                                $columnsData[$colName] = array_key_exists($colName, $clickData) ? $clickData[$colName] : null;
                            }

                            $hash = md5(
                                StatsRow::calculateTimestampHour($clickData['timestamp'])
                                    . '-'
                                    . implode('-', $columnsData)
                            );

                            if (empty($statsRows[$hash])) {
                                $statsRows[$hash] = new StatsRow($clickData['timestamp'], $columnsData);
                            }

                            if (empty($oldClicksReloaded[$click->getClickId()])) {
                                $statsRows[$hash]->addClicks(1);

                                if (!empty($clickData['isUnique'])) {
                                    $statsRows[$hash]->addClicksUnique(1);
                                }
                            }

                            if (!empty($clickData['hasAttention'])) {
                                $statsRows[$hash]->addAttention(1);
                            }

                            if (!empty($clickData['hasInterest'])) {
                                $statsRows[$hash]->addInterest(1);
                            }

                            if (!empty($clickData['hasDesire'])) {
                                $statsRows[$hash]->addDesire(1);
                            }

                            if (array_key_exists('suspiciousScore', $clickData)) {
                                $statsRows[$hash]->addSuspiciousScore($clickData['suspiciousScore']);
                            }

                            if (!empty($clickIdToActions[$click->getClickId()])) {
                                $actionsCount = count($clickIdToActions[$click->getClickId()]);
                                $actionsRevenue = 0;
                                foreach ($clickIdToActions[$click->getClickId()] as $action) {
                                    $actionsRevenue += $action->getRevenue();
                                }

                                $statsRows[$hash]->addActions($actionsCount);
                                $statsRows[$hash]->addRevenue($actionsRevenue);
                            }
                        }

                        $statsTable->insert($statsRows);
                    }

                    foreach ($filesToDelete as $file) {
                        unlink($file);
                    }

                    DB::singleton()->transactionCommit();
                } catch (\Exception $ex) {

                    DB::singleton()->transactionRollback();
                    throw $ex;
                }
            }
        });
    }

    /**
     * 
     */
    static function maxmindUpdate()
    {
        $params = [
            'blocking' => false,
            'timeout' => 60 * 15,
            'sslverify' => false,
        ];
        $endpoint = URLTools::getRestURL('/clickervolt/api/v1/updateMaxmindDBs');
        $data = wp_remote_get($endpoint, $params);
        if (is_wp_error($data)) {
            throw new \Exception($data->get_error_message());
        }
    }
}
