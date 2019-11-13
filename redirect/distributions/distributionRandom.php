<?php

namespace ClickerVolt;

require_once __DIR__ . '/distribution.php';

class DistributionRandom extends Distribution
{
    const OPTION_WEIGHTS = 'weights';
    const TYPE = 'random';

    function getType()
    {
        return self::TYPE;
    }

    function getFinalURL($urls, $slug, $options = [])
    {
        $weights = $options[self::OPTION_WEIGHTS];

        // remove entries with weight <= 0
        foreach ($weights as $i => $w) {
            if ($w <= 0) {
                unset($weights[$i]);
                unset($urls[$i]);
            }
        }

        $totalWeights = 0;
        foreach ($weights as $w) {
            $totalWeights += $w;
        }

        $rand = mt_rand(0, $totalWeights);

        $curWeight = 0;
        foreach ($urls as $i => $url) {
            $curWeight += $weights[$i];

            if ($rand <= $curWeight) {
                return $url;
            }
        }

        return $urls[0];
    }
}

Distribution::registerDistribution(DistributionRandom::TYPE, 'ClickerVolt\\DistributionRandom');
