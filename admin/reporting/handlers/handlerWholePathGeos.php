<?php

namespace ClickerVolt\Reporting;

use ClickerVolt\TableStats;


require_once __DIR__ . '/handlerBase.php';
require_once __DIR__ . '/../../../utils/countryCodes.php';

class HandlerWholePathGeos extends HandlerWholePath
{

    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {

        $table = new \ClickerVolt\TableStatsWholePathGeos();
        $tableName = $table->getName();

        $tableGeos = new \ClickerVolt\TableGeos();

        $tables = [$tableName, $tableGeos->getName() . " as geos on geos.geoHash = {$tableName}.geoHash"];
        return $this->addPathsTables($request, $tableName, $tables);
    }

    /**
     * 
     */
    public function formatValue($value, $segmentType)
    {

        switch ($segmentType) {

            case Segment::TYPE_CONNECTION_PROXY:
                $value = $value === null
                    ? TableStats::UNKNOWN
                    : ($value ? "Proxy" : "Not Proxy");
                break;

            case Segment::TYPE_CONNECTION_CELLULAR:
                $value = $value === null
                    ? TableStats::UNKNOWN
                    : ($value ? "Cellular" : "Wired/Wifi");
                break;

            case Segment::TYPE_GEO_COUNTRY:
                if (!empty(\ClickerVolt\CountryCodes::MAP[$value])) {
                    $value = \ClickerVolt\CountryCodes::MAP[$value];
                }
                break;

            default:
                $value = parent::formatValue($value, $segmentType);
                break;
        }

        return $value;
    }

    /**
     * 
     * @return []
     */
    public function getMapper($request)
    {

        // See https://propellerads.com/blog/what-is-a-tier-of-traffic-and-what-tier-should-you-choose/

        $tier1Countries = [
            "AU",       // Australia
            "AT",       // Austria
            "BE",       // Belgium
            "CA",
            "DK",
            "FI",
            "FR",
            "DE",       // Germany
            "IE",       // Ireland
            "IT",
            "LU",       // Luxembourg
            "NL",       // Netherlands
            "NZ",
            "NO",       // Norway
            "ES",       // Spain
            "SE",       // Sweden
            "CH",       // Switzerland
            "GB",       // United Kingdom
            "US",
        ];

        $tier2Countries = [
            "AD",       // Andorra
            "AR",       // Argentina
            "BS",       // Bahamas
            "BY",       // Belarus
            "BO",       // Bolivia
            "BA",       // Bosnia and Herzegovina
            "BR",       // Brazil
            "BN",       // Brunei
            "BG",       // Bulgaria
            "CL",       // Chile
            "CN",       // China
            "CO",       // Colombia
            "CR",       // Costa Rica
            "HR",       // Croatia
            "CY",       // Cyprus
            "CZ",       // Czechia
            "DO",       // Dominican Republic
            "EC",       // Ecuador
            "EG",       // Egypt
            "EE",       // Estonia
            "FJ",       // Fiji
            "GR",       // Greece
            "GY",       // Guyana
            "HK",       // Hong Kong
            "HU",       // Hungary
            "IS",       // Iceland
            "ID",       // Indonesia
            "IL",       // Israel
            "JP",       // Japan
            "KZ",       // Kazakhstan
            "LV",       // Latvia
            "LT",       // Lithuania
            "MO",       // Macao
            "MY",       // Malaysia
            "MT",       // Malta
            "MX",       // Mexico
            "ME",       // Montenegro
            "MA",       // Morocco
            "NP",       // Nepal
            "OM",       // Oman
            "PA",       // Panama
            "PY",       // Paraguay
            "PE",       // Peru
            "PH",       // Philippines
            "PL",       // Poland
            "PT",       // Portugal
            "PR",       // Puerto Rico
            "QA",       // Qatar
            "KR",       // Republic of Korea (South)
            "RO",       // Romania
            "RU",       // Russian Federation
            "SA",       // Saudi Arabia
            "RS",       // Serbia
            "SG",       // Singapore
            "SK",       // Slovakia
            "SI",       // Slovenia
            "ZA",       // South Africa
            "TH",       // Thailand
            "TR",       // Turkey
            "UA",       // Ukraine
            "AE",       // United Arab Emirates
            "UY",       // Uruguay
            "VU",       // Vanuatu
        ];

        $tier1Countries = implode(',', array_map(function ($v) {
            return "\"{$v}\"";
        }, $tier1Countries));
        $tier2Countries = implode(',', array_map(function ($v) {
            return "\"{$v}\"";
        }, $tier2Countries));

        $mapper = [

            Segment::TYPE_GEO_COUNTRY_TIER => [
                self::MAP_SELECT => "case 
                                        when geos.geoCountry in ({$tier1Countries}) then 'Tier 1'
                                        when geos.geoCountry in ({$tier2Countries}) then 'Tier 2'
                                        else 'Tier 3'
                                     end"
            ],

            Segment::TYPE_GEO_COUNTRY => [
                self::MAP_SELECT => 'geos.geoCountry'
            ],

            Segment::TYPE_GEO_REGION => [
                self::MAP_SELECT => 'geos.geoRegion'
            ],

            Segment::TYPE_GEO_CITY => [
                self::MAP_SELECT => 'geos.geoCity'
            ],

            Segment::TYPE_GEO_ZIP => [
                self::MAP_SELECT => 'geos.geoZip'
            ],

            Segment::TYPE_GEO_TIMEZONE => [
                self::MAP_SELECT => 'geos.geoTimezone'
            ],

            Segment::TYPE_CONNECTION_ISP => [
                self::MAP_SELECT => 'geos.geoIsp'
            ],

            Segment::TYPE_CONNECTION_PROXY => [
                self::MAP_SELECT => 'geos.geoIsProxy'
            ],

            Segment::TYPE_CONNECTION_CELLULAR => [
                self::MAP_SELECT => 'geos.geoIsCellular'
            ],

        ];

        return array_merge(parent::getMapper($request), $mapper);
    }
}
