<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStatsWholePathVarX.php';

TableStats::registerClass('ClickerVolt\\TableStatsWholePathVar1');
class TableStatsWholePathVar1 extends TableStatsWholePathVarX
{

    public function getVarNumber()
    {
        return 1;
    }
}
