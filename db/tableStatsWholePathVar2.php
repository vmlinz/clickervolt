<?php

namespace ClickerVolt;

require_once __DIR__ . '/tableStatsWholePathVarX.php';

TableStats::registerClass('ClickerVolt\\TableStatsWholePathVar2');
class TableStatsWholePathVar2 extends TableStatsWholePathVarX
{

    public function getVarNumber()
    {
        return 2;
    }
}
