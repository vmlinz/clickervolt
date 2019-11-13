<?php

namespace ClickerVolt;

class Sanitizer
{
    /**
     * Keys are used as internal identifiers. Lowercase alphanumeric characters, dashes and underscores are allowed.
     */
    static function sanitizeKey($val)
    {
        return self::run($val, 'sanitize_key');
    }

    static function sanitizeTextField($val)
    {
        return self::run($val, 'sanitize_text_field');
    }

    static function sanitizeURL($val)
    {
        return self::run($val, 'esc_url_raw');
    }

    static private function run($val, $callback)
    {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $val[$k] = self::run($v, $callback);
            }
        } else {
            $val = call_user_func($callback, $val);
        }
        return $val;
    }
}
