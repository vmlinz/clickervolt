<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';

class Geo implements ArraySerializer
{

    use ArraySerializerImpl;

    protected $geoHash;
    protected $geoCountry;
    protected $geoRegion;
    protected $geoCity;
    protected $geoZip;
    protected $geoTimezone;
    protected $geoIsp;
    protected $geoIsProxy;
    protected $geoIsCellular;

    function __construct($ip)
    {

        $this->ip = $ip;
    }

    function resolveGeoIP()
    {

        if (!$this->geoCountry) {

            require_once __DIR__ . '/../utils/geoIP.php';

            $resolver = new GeoIP();
            $resolver->resolve($this->ip);
            $this->geoCountry = $resolver->getCountryCode();
            $this->geoRegion = $resolver->getRegion();
            $this->geoCity = $resolver->getCity();
            $this->geoZip = $resolver->getZip();
            $this->geoTimezone = $resolver->getTimeZone();
            $this->geoIsp = $resolver->getIsp();
            $this->geoIsProxy = $resolver->isProxy();
            $this->geoIsCellular = $resolver->isCellular();

            $this->geoHash = md5(implode('-', [
                $this->geoCountry === null ? '' : strtolower($this->geoCountry),
                $this->geoRegion === null ? '' : strtolower($this->geoRegion),
                $this->geoCity === null ? '' : strtolower($this->geoCity),
                $this->geoZip === null ? '' : strtolower($this->geoZip),
                $this->geoTimezone === null ? '' : strtolower($this->geoTimezone),
                $this->geoIsp === null ? '' : strtolower($this->geoIsp),
                $this->geoIsProxy === null ? '' : $this->geoIsProxy,
                $this->geoIsCellular === null ? '' : $this->geoIsCellular,
            ]));
        }
    }

    function getGeoHash()
    {
        return $this->geoHash;
    }
}

class TableGeos extends Table
{

    /**
     * 
     */
    public function getName()
    {

        return $this->wpTableName('clickervolt_geos');
    }

    /**
     * 
     */
    public function setup($oldVersion, $newVersion)
    {

        global $wpdb;

        $tableName = $this->getName();

        if (!$this->doesTableExist()) {

            $sql = "CREATE TABLE {$tableName} (
                        `geoHash` binary(16) not null,
                        `geoCountry` char(2) null,
                        `geoRegion` varchar(45) null,
                        `geoCity` varchar(60) null,
                        `geoZip` varchar(8) null,
                        `geoTimezone` varchar(40) null,
                        `geoIsp` varchar(255) null,
                        `geoIsProxy` tinyint unsigned null,
                        `geoIsCellular` tinyint unsigned null,
                        PRIMARY KEY (`geoHash`),
                        KEY `geoCountry_idx` (`geoCountry`, `geoRegion`, `geoCity`, `geoZip`),
                        KEY `geoTimezone_idx` (`geoTimezone`),
                        KEY `geoIsp_idx` (`geoIsp`),
                        KEY `geoIsProxy_idx` (`geoIsProxy`),
                        KEY `geoIsCellular_idx` (`geoIsCellular`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param \ClickerVolt\Geo[] $geos
     * @throws \Exception
     */
    public function insert($geos)
    {

        $mapper = [
            'geoHash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'geoCountry' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 2) : self::NULL_TOKEN;
            }],
            'geoRegion' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 45) : self::NULL_TOKEN;
            }],
            'geoCity' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 60) : self::NULL_TOKEN;
            }],
            'geoZip' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 8) : self::NULL_TOKEN;
            }],
            'geoTimezone' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'geoIsp' => ['type' => '%s', 'filter' => function ($data) {
                return $data ? substr($data, 0, 255) : self::NULL_TOKEN;
            }],
            'geoIsProxy' => ['type' => '%d', 'filter' => function ($data) {
                return $data === null ? null : ($data ? 1 : 0);
            }],
            'geoIsCellular' => ['type' => '%d', 'filter' => function ($data) {
                return $data === null ? null : ($data ? 1 : 0);
            }],
        ];

        parent::insertBulk($geos, $mapper, ['insertModifiers' => ['ignore']]);
    }
}
