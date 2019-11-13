<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/handlerWholePath.php';

class HandlerWholePathVars extends HandlerWholePath
{

    /**
     * 
     * @return true if this handler can handle the specified request
     */
    function canHandle($request)
    {

        $canHandle = parent::canHandle($request);
        if ($canHandle) {

            $nbVarsInRequest = 0;
            foreach ($request->segments as $segment) {
                if ($segment->isVar()) {
                    $nbVarsInRequest++;
                }
            }

            if ($nbVarsInRequest > 1) {
                $canHandle = false;
            }
        }

        return $canHandle;
    }

    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {

        $varNum = $this->getVarNumber($request);
        $className = "\\ClickerVolt\\TableStatsWholePathVar{$varNum}";
        $table = new $className;
        $tables = [$table->getName()];
        return $this->addPathsTables($request, $table->getName(), $tables);
    }

    /**
     * 
     * @return []
     */
    protected function getMapper($request)
    {

        $varNum = $this->getVarNumber($request);
        if ($varNum === null) {
            return [];
        }

        $type = Segment::VAR_TYPES[$varNum - 1];

        $mapper = [
            $type => [
                self::MAP_SELECT => strtolower($type)
            ]
        ];

        return array_merge(parent::getMapper($request), $mapper);
    }

    /**
     * 
     */
    protected function getVarNumber($request)
    {
        foreach ($request->segments as $segment) {
            if ($segment->isVar()) {
                return array_search($segment->getType(), Segment::VAR_TYPES) + 1;
            }
        }

        return null;
    }
}
