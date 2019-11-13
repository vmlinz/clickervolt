<?php

namespace ClickerVolt;

require_once __DIR__ . '/session/sessionClick.php';

class DynamicTokens
{
    const TOKEN_CID = '[cid]';
    const TOKEN_COUNTRY = '[geo-country]';
    const TOKEN_REGION = '[geo-region]';
    const TOKEN_CITY = '[geo-city]';
    const TOKEN_ZIP = '[geo-zip]';
    const TOKEN_TIMEZONE = '[geo-timezone]';
    const TOKEN_ISP = '[isp]';
    // const TOKEN_IS_PROXY = '[is-proxy]';
    // const TOKEN_IS_CELLULAR = '[is-cellular]';
    const TOKEN_DEVICE_TYPE = '[device-type]';
    const TOKEN_DEVICE_BRAND = '[device-brand]';
    const TOKEN_DEVICE_NAME = '[device-name]';
    const TOKEN_DEVICE_OS = '[device-os]';
    const TOKEN_DEVICE_OS_VERSION = '[device-os-version]';
    const TOKEN_DEVICE_BROWSER = '[device-browser]';
    const TOKEN_DEVICE_BROWSER_VERSION = '[device-browser-version]';
    const TOKEN_DEVICE_LANGUAGE = '[device-language]';
    const TOKEN_SLUG = '[slug]';
    const TOKEN_SOURCE = '[source]';
    const TOKEN_V1 = '[v1]';
    const TOKEN_V2 = '[v2]';
    const TOKEN_V3 = '[v3]';
    const TOKEN_V4 = '[v4]';
    const TOKEN_V5 = '[v5]';
    const TOKEN_V6 = '[v6]';
    const TOKEN_V7 = '[v7]';
    const TOKEN_V8 = '[v8]';
    const TOKEN_V9 = '[v9]';
    const TOKEN_V10 = '[v10]';
    const TOKEN_EXTERNAL_ID = '[external-id]';

    const TOKENS = [
        self::TOKEN_CID,
        self::TOKEN_COUNTRY,
        self::TOKEN_REGION,
        self::TOKEN_CITY,
        self::TOKEN_ZIP,
        self::TOKEN_TIMEZONE,
        self::TOKEN_ISP,
        // self::TOKEN_IS_PROXY,
        // self::TOKEN_IS_CELLULAR,
        self::TOKEN_DEVICE_TYPE,
        self::TOKEN_DEVICE_BRAND,
        self::TOKEN_DEVICE_NAME,
        self::TOKEN_DEVICE_OS,
        self::TOKEN_DEVICE_OS_VERSION,
        self::TOKEN_DEVICE_BROWSER,
        self::TOKEN_DEVICE_BROWSER_VERSION,
        self::TOKEN_DEVICE_LANGUAGE,
        self::TOKEN_SLUG,
        self::TOKEN_SOURCE,
        self::TOKEN_V1,
        self::TOKEN_V2,
        self::TOKEN_V3,
        self::TOKEN_V4,
        self::TOKEN_V5,
        self::TOKEN_V6,
        self::TOKEN_V7,
        self::TOKEN_V8,
        self::TOKEN_V9,
        self::TOKEN_V10,
        self::TOKEN_EXTERNAL_ID,
    ];

    function replace($string, $replaceSpacesWithPluses = true)
    {
        if (strpos($string, '[') !== false) {

            $replacePairs = [];

            $clickInfo = (new SessionClick)->getClickInfo();

            foreach (self::TOKENS as $token) {
                if (strpos($string, $token) !== false) {

                    switch ($token) {

                        case self::TOKEN_CID: {
                                $replacePairs[$token] = $clickInfo->getClickId();
                            }
                            break;

                        case self::TOKEN_COUNTRY: {
                                require_once __DIR__ . '/../utils/countryCodes.php';
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = CountryCodes::MAP[$geoIp->getCountryCode()];
                            }
                            break;

                        case self::TOKEN_REGION: {
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = $geoIp->getRegion();
                            }
                            break;

                        case self::TOKEN_CITY: {
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = $geoIp->getCity();
                            }
                            break;

                        case self::TOKEN_ZIP: {
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = $geoIp->getZip();
                            }
                            break;

                        case self::TOKEN_TIMEZONE: {
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = $geoIp->getTimeZone();
                            }
                            break;

                        case self::TOKEN_ISP: {
                                $geoIp = $this->getGeoIP();
                                $replacePairs[$token] = $geoIp->getIsp();
                            }
                            break;

                            // case self::TOKEN_IS_PROXY: {
                            //     $geoIp = $this->getGeoIP();
                            //     $replacePairs[ $token ] = $geoIp->isProxy() ? 1 : 0;
                            // }
                            // break;

                            // case self::TOKEN_IS_CELLULAR: {
                            //     $geoIp = $this->getGeoIP();
                            //     $replacePairs[ $token ] = $geoIp->isCellular() ? 1 : 0;
                            // }
                            // break;

                        case self::TOKEN_DEVICE_TYPE: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = DeviceDetection::DEVICE_TYPES[$dd->getDeviceType()];
                            }
                            break;

                        case self::TOKEN_DEVICE_BRAND: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceBrand();
                            }
                            break;

                        case self::TOKEN_DEVICE_NAME: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceName();
                            }
                            break;

                        case self::TOKEN_DEVICE_OS: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceOs();
                            }
                            break;

                        case self::TOKEN_DEVICE_OS_VERSION: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceOs() . ' ' . $dd->getDeviceOsVersion();
                            }
                            break;

                        case self::TOKEN_DEVICE_BROWSER: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceBrowser();
                            }
                            break;

                        case self::TOKEN_DEVICE_BROWSER_VERSION: {
                                $dd = $this->getDeviceDetection();
                                $replacePairs[$token] = $dd->getDeviceBrowser() . ' ' . $dd->getDeviceBrowserVersion();
                            }
                            break;

                        case self::TOKEN_DEVICE_LANGUAGE: {
                                require_once __DIR__ . '/../utils/languageCodes.php';
                                $replacePairs[$token] = LanguageCodes::getBrowserLanguage();
                            }
                            break;

                        case self::TOKEN_SLUG: {
                                $replacePairs[$token] = $clickInfo->getSlug();
                            }
                            break;

                        case self::TOKEN_SOURCE: {
                                $replacePairs[$token] = $clickInfo->getSource();
                            }
                            break;

                        case self::TOKEN_V1: {
                                $replacePairs[$token] = $clickInfo->getV1();
                            }
                            break;

                        case self::TOKEN_V2: {
                                $replacePairs[$token] = $clickInfo->getV2();
                            }
                            break;

                        case self::TOKEN_V3: {
                                $replacePairs[$token] = $clickInfo->getV3();
                            }
                            break;

                        case self::TOKEN_V4: {
                                $replacePairs[$token] = $clickInfo->getV4();
                            }
                            break;

                        case self::TOKEN_V5: {
                                $replacePairs[$token] = $clickInfo->getV5();
                            }
                            break;

                        case self::TOKEN_V6: {
                                $replacePairs[$token] = $clickInfo->getV6();
                            }
                            break;

                        case self::TOKEN_V7: {
                                $replacePairs[$token] = $clickInfo->getV7();
                            }
                            break;

                        case self::TOKEN_V8: {
                                $replacePairs[$token] = $clickInfo->getV8();
                            }
                            break;

                        case self::TOKEN_V9: {
                                $replacePairs[$token] = $clickInfo->getV9();
                            }
                            break;

                        case self::TOKEN_V10: {
                                $replacePairs[$token] = $clickInfo->getV10();
                            }
                            break;

                        case self::TOKEN_EXTERNAL_ID: {
                                $replacePairs[$token] = $clickInfo->getExternalId();
                            }
                            break;
                    }
                }
            }

            if (!empty($replacePairs)) {
                if ($replaceSpacesWithPluses) {
                    foreach ($replacePairs as $k => $v) {
                        $replacePairs[$k] = str_replace(' ', '+', $v);
                    }
                }
                $string = str_replace(array_keys($replacePairs), array_values($replacePairs), $string);
            }
        }

        return $string;
    }

    /**
     * @return \ClickerVolt\GeoIP
     */
    protected function getGeoIP()
    {
        if (!$this->geoIP) {

            require_once __DIR__ . '/../utils/ipTools.php';
            require_once __DIR__ . '/../utils/geoIP.php';

            $this->geoIP = new GeoIP();
            $this->geoIP->resolve(IPTools::getUserIP());
        }

        return $this->geoIP;
    }

    /**
     * @return \ClickerVolt\DeviceDetection
     */
    protected function getDeviceDetection()
    {
        if (!$this->deviceDetection) {

            require_once __DIR__ . '/../utils/deviceDetection.php';

            $this->deviceDetection = new DeviceDetection();
            $this->deviceDetection->resolve(empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT']);
        }

        return $this->deviceDetection;
    }

    private $geoIP = null;
    private $deviceDetection = null;
}
