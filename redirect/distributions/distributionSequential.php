<?php

namespace ClickerVolt;

require_once __DIR__ . '/distribution.php';

class DistributionSequential extends Distribution
{
    const TYPE = 'sequential';

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

Distribution::registerDistribution(DistributionSequential::TYPE, 'ClickerVolt\\DistributionSequential');
