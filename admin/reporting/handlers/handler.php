<?php

namespace ClickerVolt\Reporting;

use ClickerVolt\URLsPath;


require_once __DIR__ . '/../../../db/db.php';

abstract class Handler
{
    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    abstract protected function getTableNames($request);

    /**
     * 
     * @return []
     */
    abstract protected function getMapper($request);

    /**
     * 
     * @return true if this handler can handle the specified request
     */
    function canHandle($request)
    {
        \ClickerVolt\DB::singleton()->includeAll();

        $mustIncludeSegmentTypes = [];
        foreach ($request->segments as $segment) {
            $mustIncludeSegmentTypes[$segment->getType()] = $segment->getType();
        }

        if ($request->linkIdFilters) {
            $mustIncludeSegmentTypes[Segment::TYPE_LINK] = Segment::TYPE_LINK;
        }
        if ($request->sourceIdFilters) {
            $mustIncludeSegmentTypes[Segment::TYPE_SOURCE] = Segment::TYPE_SOURCE;
        }

        $map = $this->getMapper($request);
        foreach ($mustIncludeSegmentTypes as $segmentType) {
            if (empty($map[$segmentType])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 
     * @param \ClickerVolt\Reporting\Request $request
     * @param array $options
     * @return [][] - array of rows
     */
    function getRows($request, $options = [])
    {
        $defaultOptions = [
            'formatValues' => true,
            'applyCost' => true,
        ];

        $options = array_merge($defaultOptions, $options);

        $map = $this->getMapper($request);

        $columns = [];
        $selects = [];
        $groupBys = [];

        foreach ($request->segments as $i => $segment) {
            $columns[$i] = $map[$segment->getType()][self::MAP_SELECT];
            $selects[] =  "{$columns[$i]} as segment{$i}";
            $groupBys[] = "segment{$i}";
        }

        $strSelects = empty($selects) ? '' : implode(', ', $selects) . ',';
        $strGroupBys = empty($groupBys) ? '' : 'group by ' . implode(', ', $groupBys);

        $tables = $this->getTableNames($request);
        $from = $tables[0];

        if (count($tables) > 1) {
            unset($tables[0]);
            $joins = 'left join ' . implode(' left join ', $tables);
        } else {
            $joins = '';
        }

        $conditions = [];

        $timestampCol = $this->getTimestampColumn();
        $conditions[] = "{$timestampCol} >= {$request->fromTimestamp}";
        $conditions[] = "{$timestampCol} < {$request->toTimestamp}";

        foreach ($request->segments as $i => $segment) {
            if ($segment->getFilter() !== null) {
                $conditions[] = "{$columns[$i]} = '{$segment->getFilter()}'";
            }
        }

        if ($request->linkIdFilters) {

            $linkIdConditions = [];

            if (count($request->linkIdFilters) == 1) {
                $linkIdConditions[] = $map[Segment::TYPE_LINK][self::MAP_SELECT] . " = {$request->linkIdFilters[0]}";
            } else {
                $filters = implode(',', $request->linkIdFilters);
                $linkIdConditions[] = $map[Segment::TYPE_LINK][self::MAP_SELECT] . " in ({$filters})";
            }

            if ($request->isSegmentPresent(Segment::TYPE_FUNNEL_LINK)) {

                // We are segmenting per funnel link - so we also want to get
                // rows where the path starts with the filtered link ids.

                $col = $map[Segment::TYPE_FUNNEL_LINK][self::MAP_SELECT];
                $sep = URLsPath::SEPARATOR;
                foreach ($request->linkIdFilters as $filter) {
                    $linkIdConditions[] = "{$col} like '{$filter}{$sep}%'";
                }
            }

            $conditions[] = '(' . implode(' or ', $linkIdConditions) . ')';
        }

        if ($request->sourceIdFilters) {
            if (count($request->sourceIdFilters) == 1) {
                $conditions[] = $map[Segment::TYPE_SOURCE][self::MAP_SELECT] . " = '{$request->sourceIdFilters[0]}'";
            } else {
                $filters = implode(',', array_map(function ($v) {
                    return "'{$v}'";
                }, $request->sourceIdFilters));
                $conditions[] = $map[Segment::TYPE_SOURCE][self::MAP_SELECT] . " in ({$filters})";
            }
        }

        $conditions = implode(' and ', $conditions);

        $metrics = $this->getSQLMetrics($request);
        $sql = "select 
                    {$strSelects}
                    {$metrics}
                from {$from}
                {$joins}
                where {$conditions}
                {$strGroupBys}
                order by clicks desc";

        global $wpdb;
        $rows = $wpdb->get_results($sql, ARRAY_A);

        $segmentsThatCannotBeNA = [
            Segment::TYPE_LINK,
            Segment::TYPE_URL,
            Segment::TYPE_TIME_DATES,
            Segment::TYPE_TIME_DAY_OF_WEEK,
            Segment::TYPE_TIME_HOUR_OF_DAY,
        ];

        foreach ($rows as &$row) {

            $row['cost'] = 0;

            foreach ($groupBys as $i => $by) {

                if (!in_array($request->segments[$i]->getType(), $segmentsThatCannotBeNA)) {
                    if ($row[$by] === '') {
                        $row[$by] = \ClickerVolt\TableStats::NONE;
                    } else if ($row[$by] === null) {
                        $row[$by] = \ClickerVolt\TableStats::UNKNOWN;
                    }
                } else if ($row[$by] === null) {
                    $row[$by] = '';
                }
            }

            $row['clicks']             = (int) $row['clicks'];
            $row['clicksUnique']       = (int) $row['clicksUnique'];
            $row['revenue']            = (float) $row['revenue'];
            $row['actions']            = (int) $row['actions'];
            $row['hasAttention']       = (int) $row['hasAttention'];
            $row['hasInterest']        = (int) $row['hasInterest'];
            $row['hasDesire']          = (int) $row['hasDesire'];
            $row['suspiciousScoreSum'] = (int) $row['suspiciousScoreSum'];
        }

        if ($options['applyCost']) {
            $this->applyCost($request, $rows);
        }

        if ($request->allAggregated) {
            $this->aggregateLinks($request, $rows);
        }

        if ($options['formatValues']) {
            $this->formatRows($request, $rows);
        }

        return $rows;
    }

    /**
     * @param \ClickerVolt\Reporting\Request
     * @param array $rows
     */
    protected function aggregateLinks($request, &$rows)
    {
        $funcGetRowKey = function ($request, $row, $exceptSegmentIndex) {
            $segmentVals = [];
            foreach ($request->segments as $i => $segment) {
                if ($i != $exceptSegmentIndex) {
                    $segmentVals[] = $row["segment{$i}"];
                }
            }
            return md5(implode('-', $segmentVals));
        };

        $linkSegIndex = $request->getSegmentIndex(Segment::TYPE_LINK);
        if ($linkSegIndex != -1) {
            $rowKeyToRowIndexes = [];
            foreach ($rows as $iRow => $row) {
                $key = $funcGetRowKey($request, $row, $linkSegIndex);
                $rows[$iRow]["segment{$linkSegIndex}"] = $key;
                if (!isset($rowKeyToRowIndexes[$key])) {
                    $rowKeyToRowIndexes[$key] = [];
                }
                $rowKeyToRowIndexes[$key][] = $iRow;
            }

            $newRows = [];
            foreach ($rowKeyToRowIndexes as $key => $rowIndexes) {
                $row = $this->mergeRows($request, $rows, $rowIndexes);
                $row["segment{$linkSegIndex}"] = "Aggregated Links";
                $newRows[] = $row;
            }
            $rows = $newRows;
        }
    }

    /**
     * @param array $rows
     * @param array $rowIndexes
     * @return array $row (merged)
     */
    protected function mergeRows($request, $rows, $rowIndexes)
    {
        $row = [
            'clicks' => 0,
            'clicksUnique' => 0,
            'revenue' => 0,
            'actions' => 0,
            'hasAttention' => 0,
            'hasInterest' => 0,
            'hasDesire' => 0,
            'suspiciousScoreSum' => 0,
            'cost' => 0,
            'meta' => ['costValue' => 0],
        ];
        foreach ($rowIndexes as  $i => $iRow) {
            $rowToMerge = $rows[$iRow];
            $row['clicks'] += $rowToMerge['clicks'];
            $row['clicksUnique'] += $rowToMerge['clicksUnique'];
            $row['revenue'] += $rowToMerge['revenue'];
            $row['actions'] += $rowToMerge['actions'];
            $row['hasAttention'] += $rowToMerge['hasAttention'];
            $row['hasInterest'] += $rowToMerge['hasInterest'];
            $row['hasDesire'] += $rowToMerge['hasDesire'];
            $row['suspiciousScoreSum'] += $rowToMerge['suspiciousScoreSum'];
            $row['cost'] += $rowToMerge['cost'];
            $row['meta']['costValue'] += isset($rowToMerge['meta']['costValue']) ? $rowToMerge['meta']['costValue'] : 0;

            if ($i == 0) {
                foreach ($request->segments as $i => $segment) {
                    $row["segment{$i}"] = $rowToMerge["segment{$i}"];
                }
            }
        }
        return $row;
    }

    /**
     * 
     */
    protected function applyCost($request, &$rows)
    {
        if (!empty($request->segments) && $request->segments[0]->getType() == Segment::TYPE_LINK) {

            // Get costs of all slugs present in this report

            $funnelLinkIndex = $request->getSegmentIndex(Segment::TYPE_FUNNEL_LINK);

            $allLinkIds = [];
            foreach ($rows as $row) {

                $linkId = $row['segment0'];
                $allLinkIds[$linkId] = $linkId;

                if ($funnelLinkIndex != -1) {
                    $linkIdsPath = explode(URLsPath::SEPARATOR, $row["segment{$funnelLinkIndex}"]);
                    $linkId = $linkIdsPath[count($linkIdsPath) - 1];
                    if ($linkId !== '') {
                        $allLinkIds[$linkId] = $linkId;
                    }
                }
            }

            if (!empty($allLinkIds)) {

                $allLinkIds = implode(',', $allLinkIds);

                $tableWholePath = new \ClickerVolt\TableStatsWholePath();
                $tableWholePathName = $tableWholePath->getName();

                $tableLinks = new \ClickerVolt\TableLinks();
                $tableLinksName = $tableLinks->getName();

                $sql = "select
                            stats.linkId,
                            sum(stats.clicks) as totalClicks,
                            links.costType,
                            links.costValue
                        from {$tableWholePathName} stats
                        join {$tableLinksName} links on links.id = stats.linkId
                        where stats.linkId in ({$allLinkIds})
                        group by stats.linkId";

                global $wpdb;
                $costRows = $wpdb->get_results($sql, ARRAY_A);
                $linkIdToCostData = [];

                foreach ($costRows as $costRow) {

                    if ($costRow['costType'] == \ClickerVolt\Link::COST_TYPE_TOTAL) {
                        $costRow['costType'] = \ClickerVolt\Link::COST_TYPE_CPC;
                        $costRow['costValue'] = $costRow['costValue'] / $costRow['totalClicks'];
                    }

                    $linkIdToCostData[$costRow['linkId']] = $costRow;
                }
            }

            foreach ($rows as &$row) {

                $row['cost'] = 0;

                if (!empty($linkIdToCostData)) {

                    $segmentIndex = $request->getSegmentIndex(Segment::TYPE_FUNNEL_LINK);
                    if ($segmentIndex != -1) {
                        $linkIdsPath = explode(URLsPath::SEPARATOR, $row["segment{$segmentIndex}"]);
                        $linkId = $linkIdsPath[count($linkIdsPath) - 1];
                    } else {
                        $linkId = $row["segment0"];
                    }

                    if (!empty($linkIdToCostData[$linkId])) {

                        $costRow = $linkIdToCostData[$linkId];
                        switch ($costRow['costType']) {

                            case \ClickerVolt\Link::COST_TYPE_CPC:
                                $row['cost'] = $costRow['costValue'] * $row['clicks'];
                                break;

                            case \ClickerVolt\Link::COST_TYPE_CPA:
                                $row['cost'] = $costRow['costValue'] * $row['actions'];
                                break;
                        }

                        $row['meta']['costType'] = $costRow['costType'];
                        $row['meta']['costValue'] = $costRow['costValue'];
                    }
                }
            }
        }
    }

    /**
     * 
     * @param \ClickerVolt\Reporting\Request $request
     * @param [][] $rows
     */
    protected function formatRows($request, &$rows)
    {
        foreach ($rows as &$row) {
            foreach ($request->segments as $k => $segment) {
                $row["segment{$k}"] = $this->formatValue($row["segment{$k}"], $segment->getType());
            }
        }
    }

    /**
     * 
     */
    static function getEmptyRow($request)
    {
        $emptyRow = [];

        foreach ($request->segments as $i => $segment) {
            $emptyRow["segment{$i}"] = '';
        }

        foreach (self::METRICS as $metric) {
            $emptyRow[$metric] = 0;
        }

        $emptyRow['cost'] = 0;

        return $emptyRow;
    }

    /**
     * 
     */
    protected function formatValue($value, $segmentType)
    {
        if ($segmentType == Segment::TYPE_LINK || $segmentType == Segment::TYPE_FUNNEL_LINK) {

            $value = $this->linkIdToSlug($value);
        } else if ($segmentType == Segment::TYPE_SOURCE) {

            $value = $this->sourceIdToName($value);
        }

        return $value;
    }

    /**
     * 
     */
    protected function getTimestampColumn()
    {
        return "timestampHour";
    }

    /**
     * 
     */
    protected function getSQLMetrics($request)
    {
        $sqlMetrics = [];

        foreach (self::METRICS as $metric) {
            $sqlMetrics[] = "sum({$metric}) as {$metric}";
        }

        return implode(', ', $sqlMetrics);
    }

    /**
     * 
     */
    protected function linkIdToSlug($linkId)
    {
        if (self::$linkIdsToNames === null) {

            self::$linkIdsToNames = [];

            $table = new \ClickerVolt\TableLinks();
            $links = $table->loadAll(['id', 'slug']);
            foreach ($links as $link) {
                self::$linkIdsToNames[$link->getId()] = $link->getSlug();
            }
        }

        if (!empty(self::$linkIdsToNames[$linkId])) {
            return self::$linkIdsToNames[$linkId];
        }

        return $linkId;
    }

    /**
     * 
     */
    protected function sourceIdToName($sourceId)
    {
        $prefix = '<span class="stats-segment-hint">source: </span>';

        if (self::$sourceIdsToNames === null) {

            $table = new \ClickerVolt\TableSourceTemplates();
            self::$sourceIdsToNames = $table->loadIdsToNames();
        }

        if (!empty(self::$sourceIdsToNames[$sourceId])) {
            return $prefix . self::$sourceIdsToNames[$sourceId];
        }

        return $prefix . $sourceId;
    }

    static private $linkIdsToNames = null;
    static private $sourceIdsToNames = null;

    const METRICS = [
        'clicks', 'clicksUnique', 'revenue', 'actions',
        'hasAttention', 'hasInterest', 'hasDesire',
        'suspiciousScoreSum',
    ];

    const MAP_SELECT = 'select';
}
