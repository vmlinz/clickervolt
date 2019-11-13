<?php

namespace ClickerVolt;

class Session
{
    const URL_SESSION_KEY = 'session';
    const COOKIE_NAME = 'clickervolt-sid';

    /**
     * 
     */
    function get($key)
    {
        return empty($_SESSION[$key]) ? null : $_SESSION[$key];
    }

    /**
     * 
     */
    function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 
     */
    function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            $sessionId = null;

            require_once __DIR__ . '/../../db/objects/parallelId.php';
            $pidParam = ParallelId::fromURLOrParams(null, $_GET);
            if ($pidParam) {
                $sessionId = md5($pidParam->getValue());
            }

            if (!$sessionId) {
                if (isset($_GET[self::URL_SESSION_KEY])) {
                    $sessionId = $_GET[self::URL_SESSION_KEY];
                } else {
                    $sessionId = filter_input(INPUT_COOKIE, self::COOKIE_NAME);
                }
            }

            if (!$sessionId) {
                require_once __DIR__ . '/../../utils/ipTools.php';
                $footprints = [];
                $footprints[] = IPTools::getUserIP();
                $footprints[] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
                $footprints[] = empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? '' : $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                $sessionId = md5(implode('|', $footprints));
            }

            if ($sessionId) {
                session_id($sessionId);
            }

            $sessionLifetime = 7 * 24 * 60 * 60;
            session_set_cookie_params($sessionLifetime, '/');
            session_start();

            $cookieLifetime = 365 * 24 * 60 * 60;
            setcookie(self::COOKIE_NAME, session_id(), time() + $cookieLifetime, '/');
            setcookie(session_name(), session_id(), time() + $sessionLifetime, '/');
        }
    }
}
