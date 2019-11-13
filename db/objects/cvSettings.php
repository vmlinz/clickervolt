<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/arrayVars.php';

class CVSettings
{

    const WP_OPTION_KEY = 'clickervolt-settings';

    const RECAPTCHA3_SITE_KEY = 'recaptcha3/site-key';
    const RECAPTCHA3_SECRET_KEY = 'recaptcha3/secret-key';
    const RECAPTCHA3_HIDE_BADGE = 'recaptcha3/hide-badge';
    const IP_DETECTION_TYPE = 'ip-detection';

    const VALUE_IP_DETECTION_TYPE_AUTO = 'auto';
    const VALUE_IP_DETECTION_TYPE_REMOTE_ADDR = 'REMOTE_ADDR';


    static private $settings = null;

    static function init()
    {
        if (self::$settings === null) {
            self::$settings = get_option(self::WP_OPTION_KEY);
            if (!self::$settings) {
                self::$settings = [];
            }
        }
    }

    static function update()
    {
        require_once __DIR__ . '/../../utils/dataProxy.php';

        self::init();
        update_option(self::WP_OPTION_KEY, self::$settings);

        DataProxy::expirePublicCVSettings();
        DataProxy::getPublicCVSettings();
    }

    static function set($keyPath, $value)
    {
        self::init();
        ArrayVars::setFromPath(self::$settings, $keyPath, $value);
    }

    static function get($keyPath, $default = '')
    {
        self::init();
        return ArrayVars::getFromPath(self::$settings, $keyPath, $default);
    }
}
