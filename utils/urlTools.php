<?php

namespace ClickerVolt;

class URLTools
{
    static function getHost($fromURL)
    {
        $parts = parse_url($fromURL);
        $host = '';
        if (!empty($parts['host'])) {
            $host = $parts['host'];
        }
        if (!empty($parts['port'])) {
            $host .= ':' . $parts['port'];
        }
        return $host;
    }

    /**
     * @param string $onURL
     * @param string $newHost
     * @return string new URL
     */
    static function setHost($onURL, $newHost)
    {
        $wantedParts = parse_url("http://{$newHost}");
        $curParts = parse_url($onURL);

        if (isset($wantedParts['host'])) {
            $curParts['host'] = $wantedParts['host'];
        }
        if (isset($wantedParts['port'])) {
            $curParts['port'] = $wantedParts['port'];
        } else {
            unset($curParts['port']);
        }

        return self::build_url($curParts);
    }

    static function getPath($fromURL)
    {
        $parts = parse_url($fromURL);
        $path = '';
        if (!empty($parts['path']) && $parts['path'] != '/') {
            $path = $parts['path'];
        }
        return $path;
    }

    static function getCurrentScheme()
    {
        if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        return $scheme;
    }

    static function getCurrentURL()
    {
        $host = empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
        $uri = empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'];
        return self::getCurrentScheme() . "://{$host}{$uri}";
    }

    /**
     * 
     * @param bool $forceUpdate - if true, then we re-cache the blog's base URL
     * @return string Home URL
     * @throws \Exception
     */
    static function getHomeURL($forceUpdate = false)
    {
        require_once __DIR__ . '/fileTools.php';

        $path = FileTools::getDataFolderPath('misc') . '/home-url';
        if ($forceUpdate || !file_exists($path)) {
            if (false === file_put_contents($path, home_url())) {
                throw new \Exception("Cannot write home URL to disk");
            }
        }
        $url = file_get_contents($path);
        if (empty($url) || !is_string($url)) {
            throw new \Exception("Cannot load home URL from disk");
        }
        return $url;
    }

    /**
     * 
     * @param bool $forceUpdate - if true, then we re-cache the plugin's base URL
     * @return string URL to the plugins/clickervolt folder
     * @throws \Exception
     */
    static function getPluginURL($forceUpdate = false)
    {
        require_once __DIR__ . '/fileTools.php';

        $path = FileTools::getDataFolderPath('misc') . '/plugin-url';
        if ($forceUpdate || !file_exists($path)) {
            if (false === file_put_contents($path, plugins_url() . '/clickervolt')) {
                throw new \Exception("Cannot write clickervolt plugin's URL to disk");
            }
        }
        $url = file_get_contents($path);
        if (empty($url) || !is_string($url)) {
            throw new \Exception("Cannot load clickervolt plugin's URL from disk");
        }
        return $url;
    }

    /**
     * @param string $slug
     * @param array $options
     * @return string
     */
    static function getSlugURL($slug, $options = [])
    {
        require_once __DIR__ . '/../redirect/router.php';

        $defaultOptions = [
            'pretty' => false,
            'urlOverride' => null,
            'domainOverride' => null,
        ];
        $options = array_merge($defaultOptions, $options);

        if ($options['pretty']) {
            $url = self::getHomeURL() . "/{$slug}";
            $querySep = '?';
        } else {
            $keySlug = Router::QUERY_KEY_SLUG;
            $url = self::getPluginURL() . "/go.php?{$keySlug}={$slug}";
            $querySep = '&';
        }

        if (!empty($options['urlOverride'])) {
            $keyURL = Router::QUERY_KEY_URL;
            $encodedURL = urlencode($options['urlOverride']);
            $url .= "{$querySep}{$keyURL}={$encodedURL}";
        }

        if (!empty($options['domainOverride'])) {
            $url = self::setHost($url, $options['domainOverride']);
        }

        return $url;
    }

    /**
     * 
     */
    static function getRestURL($endpoint = '')
    {
        $url = self::getHomeURL();
        if (stripos($url, '.php') === false && strpos($url, '?') === false && strpos($url, '&') === false) {
            $url = str_replace('//index.php', '/index.php', $url . '/index.php');
        }
        return self::appendQueryParams($url, 'rest_route=' . $endpoint);
    }

    /**
     * @param string $toURL
     * @param array|string $params
     */
    static function appendQueryParams($toURL, $params)
    {
        $sep = strpos($toURL, '?') === false ? '?' : '&';
        if (is_array($params)) {
            $params = http_build_query($params);
        } else {
            if (strpos($params, '?') === 0 || strpos($params, '&') === 0) {
                $params = substr($params, 1);
            }
        }
        return $toURL . $sep . $params;
    }

    /**
     * 
     */
    static function getParams($fromURL, $recursive = false)
    {
        $vars = [];
        $varQuery = parse_url($fromURL, PHP_URL_QUERY);
        if ($varQuery) {
            parse_str($varQuery, $vars);
            if (!is_array($vars)) {
                $vars = [];
            } else if ($recursive) {
                $subVars = [];
                foreach ($vars as $v) {
                    $subVars = array_merge($subVars, self::getParams($v, true));
                }
                if (!empty($subVars)) {
                    // Merge with deep vars having less priority than upper ones...
                    $vars = array_merge($subVars, $vars);
                }
            }
        }
        return $vars;
    }

    /**
     * 
     */
    static function setParams($url, $params)
    {
        $parts = parse_url($url);
        if (!empty($params)) {
            $parts['query'] = http_build_query($params);
        }
        return self::build_url($parts);
    }

    static function removeScheme($url)
    {
        if (strpos($url, 'http') === 0) {
            $url = str_replace(['https:', 'http:'], '', $url);
        }
        return $url;
    }

    static function isSamePage($url1, $url2)
    {
        $parts1 = parse_url($url1);
        $parts2 = parse_url($url2);
        if (isset($parts1['host']) && isset($parts2['host']) && $parts1['host'] == $parts2['host']) {
            if (!isset($parts1['path']) && !isset($parts2['path'])) {
                return true;
            } else if (isset($parts1['path']) && isset($parts2['path']) && $parts1['path'] == $parts2['path']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse of PHP's parse_url()
     * Source: https://stackoverflow.com/a/35207936
     */
    static private function build_url($parts)
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') . ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') . (isset($parts['user']) ? "{$parts['user']}" : '') . (isset($parts['pass']) ? ":{$parts['pass']}" : '') . (isset($parts['user']) ? '@' : '') . (isset($parts['host']) ? "{$parts['host']}" : '') . (isset($parts['port']) ? ":{$parts['port']}" : '') . (isset($parts['path']) ? "{$parts['path']}" : '') . (isset($parts['query']) ? "?{$parts['query']}" : '') . (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }
}
