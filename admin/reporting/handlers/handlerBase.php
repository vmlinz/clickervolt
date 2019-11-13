<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/handler.php';

class HandlerBase extends Handler
{
    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {
        $table = new \ClickerVolt\TableStatsBase();
        return [$table->getName()];
    }

    /**
     * 
     * @return []
     */
    protected function getMapper($request)
    {
        $timestampCol = $this->getTimestampColumn();

        $mapper = [
            Segment::TYPE_EMPTY => [
                self::MAP_SELECT => '" "'
            ],

            Segment::TYPE_TIME_DATES => [
                self::MAP_SELECT => "date(from_unixtime({$timestampCol}))"
            ],

            Segment::TYPE_TIME_DAY_OF_WEEK => [
                self::MAP_SELECT => "dayofweek(from_unixtime({$timestampCol}))"
            ],

            Segment::TYPE_TIME_HOUR_OF_DAY => [
                self::MAP_SELECT => "concat(date_format(from_unixtime({$timestampCol}), '%H'), ':00-', date_format(from_unixtime(3600 + {$timestampCol}), '%H'), ':00')"
            ],

            Segment::TYPE_SUSPICIOUS_VS_CLEAN => [
                self::MAP_SELECT => "if( suspiciousScoreSum = 0, 'All Clean', 'Suspicious' )"
            ],

            Segment::TYPE_SUSPICIOUS_BUCKETS => [
                self::MAP_SELECT => "case
                    when suspiciousScoreSum = 0 then 'All Clean'
                    when (suspiciousScoreSum / clicks) < 25 then '1 to 24%'
                    when (suspiciousScoreSum / clicks) < 50 then '25 to 49%'
                    when (suspiciousScoreSum / clicks) < 75 then '50 to 74%'
                    when (suspiciousScoreSum / clicks) < 100 then '75 to 99%'
                    when (suspiciousScoreSum / clicks) >= 100 then '100% Suspicious'
                    else null
                end"
            ],
        ];

        return $mapper;
    }

    /**
     * 
     */
    protected function formatValue($value, $segmentType)
    {
        switch ($segmentType) {

            case Segment::TYPE_TIME_DAY_OF_WEEK: {

                    $dayToString = [
                        1 => 'Sunday',
                        2 => 'Monday',
                        3 => 'Tuesday',
                        4 => 'Wednesday',
                        5 => 'Thursday',
                        6 => 'Friday',
                        7 => 'Saturday',
                    ];

                    if (!empty($dayToString[$value])) {
                        $value = $dayToString[$value];
                    }
                }
                break;

            default:
                $value = parent::formatValue($value, $segmentType);
                break;
        }

        return $value;
    }
}
