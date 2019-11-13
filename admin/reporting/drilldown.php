<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/segment.php';
require_once __DIR__ . '/handlers/handlerBase.php';
require_once __DIR__ . '/handlers/handlerWholePath.php';
require_once __DIR__ . '/handlers/handlerWholePathDevices.php';
require_once __DIR__ . '/handlers/handlerWholePathGeos.php';
require_once __DIR__ . '/handlers/handlerWholePathReferrers.php';
require_once __DIR__ . '/handlers/handlerWholePathVars.php';
require_once __DIR__ . '/handlers/handlerClicks.php';

class Drilldown
{

    /**
     * @param ClickerVolt\Reporting\Request $request
     */
    function __construct($request)
    {
        $this->request = $request;

        if (empty($this->request->segments)) {
            $this->request->segments = [new Segment(Segment::TYPE_EMPTY)];
        }
    }

    /**
     * @return [][]
     */
    function getRows()
    {

        try {

            $prevTimezone = \ClickerVolt\DB::singleton()->setTimezone('+0:00');

            $handlers = [
                new HandlerBase(),
                new HandlerWholePath(),
                new HandlerWholePathDevices(),
                new HandlerWholePathGeos(),
                new HandlerWholePathReferrers(),
                new HandlerWholePathVars(),
                new HandlerClicks(),
            ];

            foreach ($handlers as $handler) {
                if ($handler->canHandle($this->request)) {
                    return $handler->getRows($this->request);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            \ClickerVolt\DB::singleton()->setTimezone($prevTimezone);
        }

        throw new \Exception("No handler can handle this reporting request: " . json_encode($this->request));
    }

    private $request;
}

// $request = new Request();
// $request->fromTimestamp = 0;
// $request->toTimestamp = PHP_INT_MAX;
// $request->segments = [
//     new Segment( Segment::TYPE_LINK ),
//     new Segment( Segment::TYPE_URL ),
// ];

// $drilldown = new Drilldown( $request );
// $rows = $drilldown->getRows();
// $a = 0;
