<?php

namespace ClickerVolt;

require_once __DIR__ . '/fileTools.php';
require_once __DIR__ . '/../others/spyc/Spyc.php';
require_once __DIR__ . '/../others/device-detector/autoload.php';

class DeviceDetection
{
    const ENGINE_PIWIK = 'piwik';

    const DEVICE_TYPE_UNKNOWN              = 0;
    const DEVICE_TYPE_DESKTOP              = 1;
    const DEVICE_TYPE_SMARTPHONE           = 2;
    const DEVICE_TYPE_TABLET               = 3;
    const DEVICE_TYPE_FEATURE_PHONE        = 4;
    const DEVICE_TYPE_CONSOLE              = 5;
    const DEVICE_TYPE_TV                   = 6;
    const DEVICE_TYPE_CAR_BROWSER          = 7;
    const DEVICE_TYPE_SMART_DISPLAY        = 8;
    const DEVICE_TYPE_CAMERA               = 9;
    const DEVICE_TYPE_PORTABLE_MEDIA_PAYER = 10;
    const DEVICE_TYPE_PHABLET              = 11;
    const DEVICE_TYPE_BOT                  = 12;

    const DEVICE_TYPES = [
        self::DEVICE_TYPE_UNKNOWN => '?',
        self::DEVICE_TYPE_DESKTOP => 'Desktop',
        self::DEVICE_TYPE_SMARTPHONE => 'Smartphone',
        self::DEVICE_TYPE_TABLET => 'Tablet',
        self::DEVICE_TYPE_FEATURE_PHONE => 'Feature Phone',
        self::DEVICE_TYPE_CONSOLE => 'Console',
        self::DEVICE_TYPE_TV => 'TV',
        self::DEVICE_TYPE_CAR_BROWSER => 'Car browser',
        self::DEVICE_TYPE_SMART_DISPLAY => 'Smart Display',
        self::DEVICE_TYPE_CAMERA => 'Camera',
        self::DEVICE_TYPE_PORTABLE_MEDIA_PAYER => 'Portable Media Player',
        self::DEVICE_TYPE_PHABLET => 'Phablet',
        self::DEVICE_TYPE_BOT => 'Bot',
    ];

    /**
     * 
     */
    function __construct($engineType = self::ENGINE_PIWIK)
    {
        $this->engineType = $engineType;

        switch ($engineType) {

            case self::ENGINE_PIWIK:
                $this->initPiwik();
                break;

            default:
                throw new \Exception("Unknown engine '{$engineType}'");
        }
    }

    /**
     * 
     */
    function resolve($userAgent)
    {
        switch ($this->engineType) {

            case self::ENGINE_PIWIK:
                $this->resolvePiwik($userAgent);
                break;

            default:
                throw new \Exception("Unknown engine '{$engineType}'");
        }
    }

    function getDeviceType()
    {
        return $this->deviceType;
    }

    function getDeviceBrand()
    {
        return $this->deviceBrand;
    }

    function getDeviceName()
    {
        return $this->deviceName;
    }

    function getDeviceOs()
    {
        return $this->deviceOs;
    }

    function getDeviceOsVersion()
    {
        return $this->deviceOsVersion;
    }

    function getDeviceBrowser()
    {
        return $this->deviceBrowser;
    }

    function getDeviceBrowserVersion()
    {
        return $this->deviceBrowserVersion;
    }

    protected function resolvePiwik($userAgent)
    {
        $this->engine->setUserAgent($userAgent);
        $this->engine->parse();

        if ($this->engine->isBot()) {

            // handle bots,spiders,crawlers,...
            $botInfo = $this->engine->getBot();

            $this->deviceType = self::DEVICE_TYPE_BOT;
            $this->deviceBrand = empty($botInfo['category']) ? null : $botInfo['category'];
            $this->deviceName = empty($botInfo['name']) ? null : $botInfo['name'];
        } else {

            $mappings = [
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_DESKTOP => self::DEVICE_TYPE_DESKTOP,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE => self::DEVICE_TYPE_SMARTPHONE,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_TABLET => self::DEVICE_TYPE_TABLET,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_FEATURE_PHONE => self::DEVICE_TYPE_FEATURE_PHONE,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_CONSOLE => self::DEVICE_TYPE_CONSOLE,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_TV => self::DEVICE_TYPE_TV,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_CAR_BROWSER => self::DEVICE_TYPE_CAR_BROWSER,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_SMART_DISPLAY => self::DEVICE_TYPE_SMART_DISPLAY,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_CAMERA => self::DEVICE_TYPE_CAMERA,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_PORTABLE_MEDIA_PAYER => self::DEVICE_TYPE_PORTABLE_MEDIA_PAYER,
                \DeviceDetector\Parser\Device\DeviceParserAbstract::DEVICE_TYPE_PHABLET => self::DEVICE_TYPE_PHABLET,
            ];

            $deviceType = $this->engine->getDevice();
            if (isset($mappings[$deviceType])) {
                $this->deviceType = $mappings[$deviceType];
            } else {
                $this->deviceType = self::DEVICE_TYPE_UNKNOWN;
            }

            $this->deviceBrand = $this->engine->getBrandName();
            $this->deviceName = $this->engine->getModel();
            $this->deviceOs = $this->engine->getOs('name');
            $this->deviceOsVersion = $this->engine->getOs('version');

            $clientInfo = $this->engine->getClient();
            $this->deviceBrowser = empty($clientInfo['name']) ? null : $clientInfo['name'];
            $this->deviceBrowserVersion = empty($clientInfo['version']) ? null : $clientInfo['version'];
        }
    }

    /**
     * 
     */
    protected function initPiwik()
    {
        $this->engine = new \DeviceDetector\DeviceDetector();
        $this->engine->setCache(new PiwikCache);
    }

    /**
     * 
     */
    static function reCache()
    {
        $cache = new PiwikCache();
        $cache->flushAll();

        require_once __DIR__ . '/uasArray.php';

        $dd = new DeviceDetection();
        $uas = getUserAgents();
        foreach ($uas as $ua) {
            $dd->resolve($ua);
        }
    }

    private $engineType;
    private $engine;
    private $deviceType;
    private $deviceBrand;
    private $deviceName;
    private $deviceOs;
    private $deviceOsVersion;
    private $deviceBrowser;
    private $deviceBrowserVersion;
}

class PiwikCache implements \DeviceDetector\Cache\Cache
{
    function fetch($id)
    {
        if (!$this->contains($id)) {
            return null;
        }

        $fullPath = $this->getFilePath($id);
        $fileHandler = fopen($fullPath, 'rt');
        flock($fileHandler, LOCK_EX);
        $fileSize = filesize($fullPath);
        $content = $fileSize ? fread($fileHandler, filesize($fullPath)) : null;
        fclose($fileHandler);

        $data = json_decode($content, true);
        return $data ?: null;
    }

    function contains($id)
    {
        return file_exists($this->getFilePath($id));
    }

    function save($id, $data, $lifeTime = 0)
    {
        file_put_contents($this->getFilePath($id), json_encode($data), LOCK_EX);
    }

    function delete($id)
    {
        $path = $this->getFilePath($id);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    function flushAll()
    {
        $folder = $this->getFolderPath();
        $files = glob("{$folder}/*");

        foreach ($files as $file) {

            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function getFilePath($id)
    {
        $id = strtolower(md5($id));
        $path = $this->getFolderPath() . "/{$id}";
        return $path;
    }

    protected function getFolderPath()
    {
        return FileTools::getDataFolderPath('device_cache');
    }
}
