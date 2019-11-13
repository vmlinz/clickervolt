<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/segment.php';

class Request
{
    const LINKS_ALL_AGGREGATED = 'all_links_aggregated';
    const LINKS_ALL_SEPARATED = 'all_links_separated';

    static $linksAllOptions = [
        self::LINKS_ALL_AGGREGATED,
        self::LINKS_ALL_SEPARATED,
    ];

    public $fromTimestamp;  // inclusive
    public $toTimestamp;    // exclusive
    public $segments;       // ClickerVolt\Reporting\Segment[]

    public $allAggregated;     // true/false
    public $linkIdFilters;     // Independently from groupings, retrieve stats for those slugs only - if empty, filter is off. 
    public $sourceIdFilters;   // Independently from groupings, retrieve stats for those sources only - if empty, filter is off

    /**
     * 
     * @param string $segmentType
     * @return bool
     */
    function isSegmentPresent($segmentType)
    {
        return $this->getSegmentIndex($segmentType) != -1;
    }

    /**
     * 
     * @param string $segmentType
     * @return int - index of segment in request or -1 if not found
     */
    function getSegmentIndex($segmentType)
    {
        foreach ($this->segments as $i => $segment) {
            if ($segment->getType() == $segmentType) {
                return $i;
            }
        }
        return -1;
    }
}
