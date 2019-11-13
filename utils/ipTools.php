<?php

namespace ClickerVolt;

class IPTools
{

    /**
     * 
     * @param string $ip
     * @return string or null if invalid
     */
    static function filterIP($ip)
    {
        $parts = explode(',', $ip);

        foreach ($parts as $part) {
            if (!filter_var($part, FILTER_VALIDATE_IP)) {
                $ip = null;
            } else {
                $ip = $part;
                break;
            }
        }
        return $ip;
    }

    /**
     * 
     * @return type
     */
    static function getUserIP()
    {
        require_once __DIR__ . '/dataProxy.php';
        require_once __DIR__ . '/../db/objects/cvSettings.php';

        $ip = null;

        $settings = DataProxy::getPublicCVSettings();
        if (isset($settings[CVSettings::IP_DETECTION_TYPE])) {

            switch ($settings[CVSettings::IP_DETECTION_TYPE]) {
                case CVSettings::VALUE_IP_DETECTION_TYPE_REMOTE_ADDR:
                    if (isset($_SERVER['REMOTE_ADDR'])) {
                        $ip = self::filterIP($_SERVER['REMOTE_ADDR']);
                    }
                    return $ip;
            }
        }

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = self::filterIP($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        if (!$ip && isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = self::filterIP($_SERVER['HTTP_CLIENT_IP']);
        }

        if (!$ip && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = self::filterIP($_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        if (!$ip && isset($_SERVER['HTTP_FORWARDED'])) {
            $parts = explode('=', $_SERVER['HTTP_FORWARDED']);
            if (count($parts) == 2 && strtolower($parts['0']) == 'for') {
                $ip = self::filterIP($parts[1]);
            }
        }

        if (!$ip && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = self::filterIP($_SERVER['REMOTE_ADDR']);
        }

        return $ip;
    }

    /**
     * 
     * @param string $ip
     * @param string $range
     * 
     *     IPv4 Supported formats:
     *       1.2.3.4 for a single IP
     *       1.2.3.* where * are wildcards
     *       1.2.3.4-5.6.7.8 for an IP range
     *       1.2.3.4/24 or 1.2.3.4/255.255.255.0 for an IP range in CIDR format
     *     
     *     IPv6 Supported formats:
     *       2401:fa00:c:14:65c5:ab2b:3ebe:41d9 for a single IP
     *       2001:5c0:1400::/39 for an IP range in CIDR format
     * 
     * @return bool
     */
    static function isInRange($ip, $range)
    {
        require_once __DIR__ . '/../others/ip-lib/ip-lib.php';

        $address = \IPLib\Factory::addressFromString($ip);
        if (!$address) {
            return false;
        }

        $rangeParts = explode('-', $range);
        if (count($rangeParts) == 2) {
            $rangeParts[0] = trim($rangeParts[0]);
            $rangeParts[1] = trim($rangeParts[1]);
            $range1 = \IPLib\Factory::rangeFromString($rangeParts[0]);
            $range2 = \IPLib\Factory::rangeFromString($rangeParts[1]);
            if (!$range1 || !$range2) {
                return false;
            }

            $start = $range1->getComparableStartString();
            $end = $range2->getComparableEndString();
            $current = $address->getComparableString();
            return $current >= $start && $current <= $end;
        }

        $range = \IPLib\Factory::rangeFromString($range);
        if (!$range) {
            return false;
        }

        return $range->contains($address);
    }
}
