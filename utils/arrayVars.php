<?php

namespace ClickerVolt;

class ArrayVars
{
    /**
     * 
     */
    static function queryGet($varKey, $default = null)
    {
        return self::get($_GET, $varKey, $default);
    }

    /**
     * 
     */
    static function get(&$array, $varKey, $default = null)
    {
        return array_key_exists($varKey, $array) ? $array[$varKey] : $default;
    }

    /**
     *
     * Returns an array's entry if it is set, $default otherwise
     *
     * @param array $array - the array to get the value from
     * @param string $path - the key. It can be a path where all keys are separated by '/'
     *                      For example the keyPath 'key1/key2/key3' would translate to:
     *                      $array['key1']['key2']['key3'].
     * @param mixed $default - default value if key not found
     */
    static function getFromPath($array, $path, $default = null)
    {
        $val = $default;

        if (!empty($array)) {
            $keys = explode('/', $path);
            $nbKeys = count($keys);

            for ($i = 0; $i < $nbKeys; $i++) {
                $key = $keys[$i];

                if (!array_key_exists($key, $array)) {
                    break;
                }

                if ($i == ($nbKeys - 1)) {
                    $val = $array[$key];
                } else {
                    $array = $array[$key];
                }
            }
        }

        return $val;
    }

    /**
     * 
     */
    static function setFromPath(&$array, $path, $value)
    {
        $keys = explode('/', $path);
        $nbKeys = count($keys);

        foreach ($keys as $i => $key) {

            if ($i == ($nbKeys - 1)) {
                $array[$key] = $value;
            } else {
                if (!array_key_exists($key, $array)) {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
        }
    }
}
