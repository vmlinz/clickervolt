<?php

namespace ClickerVolt\Reporting;

class Segment
{
    const TYPE_EMPTY = 'Empty';
    const TYPE_CLICK_ID = 'Click ID';
    const TYPE_LINK = 'Link';
    const TYPE_FUNNEL_LINK = 'Funnel Links';
    const TYPE_SOURCE = 'Source';
    const TYPE_SUSPICIOUS_VS_CLEAN = 'Suspicious VS Clean';
    const TYPE_SUSPICIOUS_BUCKETS = 'Suspicious Buckets';
    const TYPE_URL = 'URL';
    const TYPE_DEVICE_TYPE = 'Device Type';
    const TYPE_DEVICE_BRAND = 'Device Brand';
    const TYPE_DEVICE_NAME = 'Device Name';
    const TYPE_DEVICE_OS = 'OS';
    const TYPE_DEVICE_OS_VERSION = 'OS Version';
    const TYPE_DEVICE_BROWSER = 'Browser';
    const TYPE_DEVICE_BROWSER_VERSION = 'Browser Version';
    const TYPE_GEO_COUNTRY_TIER = 'Country Tier';
    const TYPE_GEO_COUNTRY = 'Country';
    const TYPE_GEO_REGION = 'Region';
    const TYPE_GEO_CITY = 'City';
    const TYPE_GEO_ZIP = 'ZIP';
    const TYPE_GEO_TIMEZONE = 'Timezone';
    const TYPE_LANGUAGE = 'Language';
    const TYPE_CONNECTION_ISP = 'ISP';
    const TYPE_CONNECTION_PROXY = 'Proxy';
    const TYPE_CONNECTION_CELLULAR = 'Connection Type';
    const TYPE_IP_RANGE_C = 'IP-Range 1.2.3.xxx';
    const TYPE_IP_RANGE_B = 'IP-Range 1.2.xxx.xxx';
    const TYPE_REFERRER = 'Referrer';
    const TYPE_REFERRER_DOMAIN = 'Referrer Domain';
    const TYPE_VAR_1 = 'V1';
    const TYPE_VAR_2 = 'V2';
    const TYPE_VAR_3 = 'V3';
    const TYPE_VAR_4 = 'V4';
    const TYPE_VAR_5 = 'V5';
    const TYPE_VAR_6 = 'V6';
    const TYPE_VAR_7 = 'V7';
    const TYPE_VAR_8 = 'V8';
    const TYPE_VAR_9 = 'V9';
    const TYPE_VAR_10 = 'V10';
    const TYPE_TIME_DATES = 'Date';
    const TYPE_TIME_DAY_OF_WEEK = 'Day of Week';
    const TYPE_TIME_HOUR_OF_DAY = 'Hour of Day';

    const VAR_TYPES = [
        self::TYPE_VAR_1,
        self::TYPE_VAR_2,
        self::TYPE_VAR_3,
        self::TYPE_VAR_4,
        self::TYPE_VAR_5,
        self::TYPE_VAR_6,
        self::TYPE_VAR_7,
        self::TYPE_VAR_8,
        self::TYPE_VAR_9,
        self::TYPE_VAR_10,
    ];

    function __construct($type, $filter = null)
    {
        $this->type = $type;
        $this->filter = $filter !== '' ? $filter : null;
    }

    function getType()
    {
        return $this->type;
    }

    function getFilter()
    {
        return $this->filter;
    }

    function isVar()
    {
        return in_array($this->getType(), self::VAR_TYPES);
    }

    private $type;
    private $filter;
}
