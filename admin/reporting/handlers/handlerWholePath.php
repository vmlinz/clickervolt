<?php

namespace ClickerVolt\Reporting;

use ClickerVolt\URLsPath;
use ClickerVolt\TableURLsPaths;


require_once __DIR__ . '/handlerBase.php';

class HandlerWholePath extends HandlerBase
{
    const FUNNEL_LINK_PREFIX = '#funnel#';

    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {
        $tables = [];

        $tableWholePath = new \ClickerVolt\TableStatsWholePath();
        $tables[] = $tableWholePath->getName();

        return $this->addPathsTables($request, $tableWholePath->getName(), $tables);
    }

    /**
     * 
     */
    protected function addPathsTables($request, $mainTableName, $existingTables)
    {
        if ($request->isSegmentPresent(Segment::TYPE_FUNNEL_LINK)) {

            $tableURLsPaths = new TableURLsPaths();
            $name = $tableURLsPaths->getName();

            $existingTables[] = "{$name} as urlsPaths on urlsPaths.hash = {$mainTableName}.urlsPathHash";
        }

        return $existingTables;
    }

    /**
     * 
     * @param \ClickerVolt\Reporting\Request $request
     * @return [][] - array of rows
     */
    function getRows($request, $options = [])
    {
        $rows = parent::getRows($request, array_merge($options, [
            'formatValues' => false,
        ]));

        $funnelLinkSegIndex = $request->getSegmentIndex(Segment::TYPE_FUNNEL_LINK);
        if ($funnelLinkSegIndex !== -1) {

            // Split cells that have the URLsPath::SEPARATOR
            // and add as many column segments as needed

            $linkSegIndex = $request->getSegmentIndex(Segment::TYPE_LINK);
            $linkSegKey = "segment{$linkSegIndex}";
            $funnelLinkSegKey = "segment{$funnelLinkSegIndex}";

            foreach ($rows as $k => $row) {
                $parts = explode(URLsPath::SEPARATOR, $row[$funnelLinkSegKey]);
                if (count($parts) == 1) {
                    $rows[$k][$funnelLinkSegKey] = '';
                } else {
                    $rows[$k][$linkSegKey] = $parts[0];
                    if (count($parts) > 1) {
                        unset($parts[0]);
                        $rows[$k][$funnelLinkSegKey] = implode(URLsPath::SEPARATOR, $parts);
                        if (count($parts) == 1) {
                            $rows[$k][$funnelLinkSegKey] = self::FUNNEL_LINK_PREFIX . $this->formatValue($rows[$k][$funnelLinkSegKey], Segment::TYPE_FUNNEL_LINK);
                        }
                    }
                }
            }

            $maxColumnsToAdd = 0;
            $cellsToSplit = [];

            foreach ($rows as $y => $row) {
                $columnsToAdd = 0;
                $x = 0;
                foreach ($row as $segmentKey => $val) {
                    if (strpos($segmentKey, 'segment') === false) {
                        break;
                    }
                    if (strpos($val, URLsPath::SEPARATOR) !== false) {
                        $parts = explode(URLsPath::SEPARATOR, $val);
                        $columnsToAdd += count($parts) - 1;
                        $cellsToSplit[$y][] = $x;
                    }
                    $x++;
                }
                $maxColumnsToAdd = max($maxColumnsToAdd, $columnsToAdd);
            }

            if ($maxColumnsToAdd > 0) {

                $nbSegments = 0;
                foreach ($rows[0] as $k => $v) {
                    if (strpos($k, 'segment') === 0) {
                        $nbSegments++;
                    } else {
                        break;
                    }
                }

                foreach ($rows as $y => $row) {

                    $rowKeys = array_keys($row);
                    $rowValues = array_values($row);

                    if (empty($cellsToSplit[$y])) {

                        // No cell to split on this row, just add columns at the end of the current segments...
                        for ($i = 0; $i < $maxColumnsToAdd; $i++) {
                            array_splice($rowKeys, $nbSegments, 0, '');
                            array_splice($rowValues, $nbSegments, 0, '');
                        }
                    } else {

                        // Reverse x array to work from right to left 
                        $xs = array_reverse($cellsToSplit[$y]);
                        $addedColumns = 0;
                        foreach ($xs as $x) {

                            $parts = explode(URLsPath::SEPARATOR, $rowValues[$x]);
                            for ($i = 0; $i < count($parts) - 1; $i++) {
                                array_splice($rowKeys, $x + 1, 0, '');
                                array_splice($rowValues, $x + 1, 0, '');
                                $addedColumns++;
                            }
                            foreach ($parts as $i => $part) {
                                if ($x == $funnelLinkSegIndex) {
                                    $part = self::FUNNEL_LINK_PREFIX . $this->formatValue($part, Segment::TYPE_FUNNEL_LINK);
                                }
                                $rowValues[$x + $i] = $part;
                            }
                        }

                        while ($addedColumns < $maxColumnsToAdd) {
                            array_splice($rowKeys, $nbSegments + $addedColumns, 0, '');
                            array_splice($rowValues, $nbSegments + $addedColumns, 0, '');
                            $addedColumns++;
                        }
                    }

                    for ($i = 0; $i < $nbSegments + $maxColumnsToAdd; $i++) {
                        $rowKeys[$i] = "segment{$i}";
                    }

                    $newRow = [];
                    foreach ($rowKeys as $i => $key) {
                        $newRow[$key] = $rowValues[$i];
                    }

                    $rows[$y] = $newRow;
                }
            }

            // Now move all the empty segments to the end

            foreach ($rows as $y => $row) {

                $rowKeys = array_keys($row);
                $rowValues = array_values($row);

                $segmentValues = [];
                $segmentEmptyValues = [];

                foreach ($rowValues as $i => $val) {
                    if (strpos($rowKeys[$i], 'segment') === 0) {
                        if ($val !== '') {
                            $segmentValues[] = $val;
                        } else {
                            $segmentEmptyValues[] = '';
                        }
                    }
                }

                if (!empty($segmentEmptyValues)) {

                    $segmentValues = array_merge($segmentValues, $segmentEmptyValues);
                    foreach ($segmentValues as $i => $v) {
                        $rowValues[$i] = $v;
                    }

                    $newRow = [];
                    foreach ($rowKeys as $i => $key) {
                        $newRow[$key] = $rowValues[$i];
                    }

                    $rows[$y] = $newRow;
                }
            }
        }

        $this->formatRows($request, $rows);

        return $rows;
    }

    /**
     * 
     * @param \ClickerVolt\Reporting\Request $request
     * @return []
     */
    protected function getMapper($request)
    {
        $hasFunnelLink = $request->isSegmentPresent(Segment::TYPE_FUNNEL_LINK);

        $mapper = [

            Segment::TYPE_LINK => [
                self::MAP_SELECT => 'linkId'
            ],

            Segment::TYPE_FUNNEL_LINK => [
                self::MAP_SELECT => 'linkIdsPath'
            ],

            Segment::TYPE_SOURCE => [
                self::MAP_SELECT => 'source'
            ],

            Segment::TYPE_URL => [
                self::MAP_SELECT => $hasFunnelLink ? 'urlsPaths.path' : 'url'
            ],
        ];

        return array_merge(parent::getMapper($request), $mapper);
    }
}
