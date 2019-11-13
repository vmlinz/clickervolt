<?php

namespace ClickerVolt;

require_once __DIR__ . '/distribution.php';

class DistributionAutoOptim extends Distribution
{
    const TYPE = 'auto-optim';

    function getType()
    {
        return self::TYPE;
    }

    function getFinalURL($urls, $slug, $options = [])
    {

        $key = array_rand($urls);
        return $urls[$key];
    }
}

Distribution::registerDistribution(DistributionAutoOptim::TYPE, 'ClickerVolt\\DistributionAutoOptim');
