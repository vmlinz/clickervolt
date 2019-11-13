<?php

namespace ClickerVolt;

class StringTools
{
    static function getClassNameFromClassPath($classPath)
    {
        $parts = explode('\\', $classPath);
        $classname = $parts[count($parts) - 1];
        return $classname;
    }

    static function getFileNameFromClassPath($classPath)
    {
        return lcfirst(self::getClassNameFromClassPath($classPath)) . '.php';
    }

    /**
     * UTF8 strtolower
     */
    static function toLower($str)
    {
        if (extension_loaded('mbstring') && function_exists('\mb_strtolower')) {
            return mb_strtolower($str);
        }
        return strtolower($str);
    }

    /**
     * @param string $pattern - can include * wildcards
     * @param string $haystack
     * @param bool $caseSensitive
     * @return bool
     */
    static function isMatching($pattern, $haystack, $caseSensitive = false)
    {
        $flags = $caseSensitive ? '' : 'i';
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*?', $pattern);
        return (bool) preg_match('/^' . $pattern . '$/' . $flags, $haystack);
    }

    /**
     * Works like str_replace but can use * wildcards in the pattern
     * 
     * @param string $pattern
     * @param string $replaceWith
     * @param string $string
     * @param bool $caseSensitive
     * @return string
     */
    static function wildcardStrReplace($pattern, $replaceWith, $string, $caseSensitive = false)
    {
        $flags = $caseSensitive ? 's' : 'si';
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '(.*?)', $pattern);
        return preg_replace('/' . $pattern . '/' . $flags, $replaceWith, $string);
    }
}
