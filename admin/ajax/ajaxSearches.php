<?php

namespace ClickerVolt;

require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/../../utils/fileTools.php';

class AjaxSearches extends Ajax
{

    /**
     * 
     */
    static function searchISPs()
    {
        $search = Sanitizer::sanitizeTextField($_POST['search']);
        $isps = FileTools::searchInFile(__DIR__ . '/../../../clickervolt-data/maxmind_dbs/asn-list.txt', strtolower($search));
        return ['search' => $search, 'results' => $isps];
    }

    /**
     * 
     */
    static function searchRegions()
    {
        $search = Sanitizer::sanitizeTextField($_POST['search']);
        $regions = FileTools::searchInFile(__DIR__ . '/../../../clickervolt-data/maxmind_dbs/region-list.txt', strtolower($search));
        return ['search' => $search, 'results' => $regions];
    }

    /**
     * 
     */
    static function searchCities()
    {
        $search = Sanitizer::sanitizeTextField($_POST['search']);
        $cities = FileTools::searchInFile(__DIR__ . '/../../../clickervolt-data/maxmind_dbs/city-list.txt', strtolower($search));
        return ['search' => $search, 'results' => $cities];
    }

    /**
     * 
     */
    static function searchDeviceNames()
    {
        $search = Sanitizer::sanitizeTextField($_POST['search']);
        $names = FileTools::searchInFile(__DIR__ . '/../../others/device-detector/device-names.txt', strtolower($search));
        return ['search' => $search, 'results' => $names];
    }
};
