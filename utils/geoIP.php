<?php

namespace ClickerVolt;

require_once __DIR__ . '/../utils/arrayVars.php';

// IP-API.com -> http://ip-api.com/docs/api:returned_values
// http://ip-api.com/json/1.39.9.56?fields=213818
// max requests: 150 per minute. IP banned automatically if more requests are done in a minute

class GeoIP
{
    const ENGINE_IP_API = 'ip-api';
    const ENGINE_MAXMIND = 'maxmind';

    function __construct($engine = self::ENGINE_MAXMIND)
    {
        $this->engine = $engine;
    }

    /**
     * 
     */
    function resolve($ip)
    {
        $this->reset();
        $this->ip = $ip;

        switch ($this->engine) {

            case self::ENGINE_IP_API:
                $this->getFromIpApi();
                break;

            case self::ENGINE_MAXMIND:
                $this->getFromMaxmind();
                break;

            default:
                throw new \Exception("Unknown engine '{$this->engine}'");
        }
    }

    function getCountryCode()
    {
        return $this->countryCode;
    }
    function getRegion()
    {
        return $this->region;
    }
    function getCity()
    {
        return $this->city;
    }
    function getZip()
    {
        return $this->zip;
    }
    function getTimeZone()
    {
        return $this->timeZone;
    }
    function getIsp()
    {
        return $this->isp;
    }
    function isProxy()
    {
        return $this->isProxy;
    }
    function isCellular()
    {
        return $this->isCellular;
    }

    /**
     * 
     */
    protected function getFromIpApi()
    {
        require_once __DIR__ . '/remote.php';

        list($data, $lastURL) = Remote::singleton()->get("http://ip-api.com/json/{$this->ip}?fields=213818");
        $data = json_decode($data, true);
        if ($data) {

            $mappings = [
                'countryCode' => 'countryCode',
                'regionName' => 'region',
                'city' => 'city',
                'zip' => 'zip',
                'timezone' => 'timeZone',
                'isp' => 'isp',
                'proxy' => 'isProxy',
                'mobile' => 'isCellular',
            ];

            $this->populate($data, $mappings);
        }
    }

    /**
     * 
     */
    protected function getFromMaxmind()
    {
        require_once __DIR__ . '/../others/maxmind/autoload.php';

        $pathPrefix = FileTools::getDataFolderPath('maxmind_dbs');
        $readerCity = new \MaxMind\Db\Reader("{$pathPrefix}/city.mmdb");
        $readerASN  = new \MaxMind\Db\Reader("{$pathPrefix}/asn.mmdb");

        try {
            $cityMappings = [
                'country/iso_code' => 'countryCode',
                // 'subdivisions/1/names/en' => 'region',
                'subdivisions/0/names/en' => 'region',
                'city/names/en' => 'city',
                'postal/code' => 'zip',
                'location/time_zone' => 'timeZone',
            ];
            $this->populate($readerCity->get($this->ip), $cityMappings);

            $asnMappings = [
                'autonomous_system_organization' => 'isp',
                // 'proxy' => 'isProxy',
                // 'mobile' => 'isCellular',
            ];
            $this->populate($readerASN->get($this->ip), $asnMappings);
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            $readerCity->close();
            $readerASN->close();
        }
    }

    /**
     * 
     */
    protected function populate($data, $mappings)
    {
        $alreadyResolved = [];
        foreach ($mappings as $kSource => $kDest) {
            if (empty($alreadyResolved[$kDest])) {
                $this->{$kDest} = ArrayVars::getFromPath($data, $kSource);
                if (!empty($this->{$kDest})) {
                    $alreadyResolved[$kDest] = true;
                }
            }
        }
    }

    /**
     * 
     */
    protected function reset()
    {
        $this->ip = null;
        $this->countryCode = null;
        $this->region = null;
        $this->city = null;
        $this->zip = null;
        $this->timeZone = null;
        $this->isp = null;
        $this->isProxy = null;
        $this->isCellular = null;
    }

    private $engine;
    private $ip;
    private $countryCode;
    private $region;
    private $city;
    private $zip;
    private $timeZone;
    private $isp;
    private $isProxy;
    private $isCellular;
}
