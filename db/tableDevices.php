<?php

namespace ClickerVolt;

require_once __DIR__ . '/table.php';

class Device implements ArraySerializer
{
    use ArraySerializerImpl;

    private $userAgent;
    private $deviceHash;
    private $deviceType;
    private $deviceBrand;
    private $deviceName;
    private $deviceOs;
    private $deviceOsVersion;
    private $deviceBrowser;
    private $deviceBrowserVersion;
    private $deviceLanguage;

    function __construct($userAgent, $deviceLanguage)
    {
        $this->userAgent = $userAgent;
        $this->deviceLanguage = $deviceLanguage;
        $this->deviceHash = md5(implode('-', [
            $userAgent ? strtolower($userAgent) : '',
            $deviceLanguage ? strtolower($deviceLanguage) : ''
        ]));
    }

    function resolveDeviceData()
    {
        if ($this->deviceType === null) {
            require_once __DIR__ . '/../utils/deviceDetection.php';

            try {
                $resolver = new DeviceDetection();
                $resolver->resolve($this->userAgent);
                $this->deviceType = $resolver->getDeviceType();
                $this->deviceBrand = $resolver->getDeviceBrand();
                $this->deviceName = $resolver->getDeviceName();
                $this->deviceOs = $resolver->getDeviceOs();
                $this->deviceOsVersion = $resolver->getDeviceOsVersion();
                $this->deviceBrowser = $resolver->getDeviceBrowser();
                $this->deviceBrowserVersion = $resolver->getDeviceBrowserVersion();
            } catch (\Exception $ex) {
                require_once __DIR__ . '/../utils/logger.php';
                Logger::getErrorLogger()->log($ex);
            }
        }
    }

    function getDeviceHash()
    {
        return $this->deviceHash;
    }
}

class TableDevices extends Table
{
    /**
     * 
     */
    public function getName()
    {
        return $this->wpTableName('clickervolt_devices');
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
                        `deviceHash` binary(16) not null,
                        `deviceType` varchar(40) null,
                        `deviceBrand` varchar(40) null,
                        `deviceName` varchar(40) null,
                        `deviceOs` varchar(40) null,
                        `deviceOsVersion` varchar(40) null,
                        `deviceBrowser` varchar(40) null,
                        `deviceBrowserVersion` varchar(40) null,
                        `deviceLanguage` varchar(5) null,
                        `userAgent` varchar(255) not null,
                        PRIMARY KEY (`deviceHash`),
                        KEY `deviceType_idx` (`deviceType`),
                        KEY `deviceBrand_idx` (`deviceBrand`),
                        KEY `deviceName_idx` (`deviceName`),
                        KEY `deviceOs_idx` (`deviceOs`, `deviceOsVersion`),
                        KEY `deviceBrowser_idx` (`deviceBrowser`, `deviceBrowserVersion`),
                        KEY `deviceLanguage_idx` (`deviceLanguage`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $res = $wpdb->query($sql);
            if ($res === false) {
                throw new \Exception("Cannot create table {$tableName}: {$wpdb->last_error}");
            }
        } else if ($oldVersion) { }
    }

    /**
     * @param \ClickerVolt\Device[] $devices
     * @throws \Exception
     */
    public function insert($devices)
    {
        $mapper = [
            'deviceHash' => ['type' => '%s', 'filter' => function ($data) {
                return hex2bin($data);
            }],
            'deviceType' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceBrand' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceName' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceOs' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceOsVersion' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceBrowser' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceBrowserVersion' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 40) : self::NULL_TOKEN;
            }],
            'deviceLanguage' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 5) : self::NULL_TOKEN;
            }],
            'userAgent' => ['type' => '%s', 'filter' => function ($data) {
                $data = $this->rewriteUnknown($data);
                return $data ? substr($data, 0, 255) : self::NULL_TOKEN;
            }],
        ];

        $updateKeys = [
            'deviceType' => 'values(deviceType)',
            'deviceBrand' => 'values(deviceBrand)',
            'deviceName' => 'values(deviceName)',
            'deviceOs' => 'values(deviceOs)',
            'deviceOsVersion' => 'values(deviceOsVersion)',
            'deviceBrowser' => 'values(deviceBrowser)',
            'deviceBrowserVersion' => 'values(deviceBrowserVersion)',
            'deviceLanguage' => 'values(deviceLanguage)',
        ];

        parent::insertBulk($devices, $mapper, ['insertModifiers' => ['ignore'], 'onDuplicateKeyUpdate' => $updateKeys]);
    }

    /**
     * 
     */
    private function rewriteUnknown($value)
    {
        require_once __DIR__ . '/../others/device-detector/DeviceDetector.php';
        if ($value === \DeviceDetector\DeviceDetector::UNKNOWN) {
            $value = null;
        }
        return $value;
    }
}
