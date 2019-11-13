<?php

namespace ClickerVolt;

class CookieTools
{

    /**
     * 
     */
    static function isLoggedIn()
    {

        if (count($_COOKIE)) {
            foreach ($_COOKIE as $key => $val) {
                if (strpos($key, "wordpress_logged_in") === 0 && strlen($val) > 5) {
                    return true;
                }
            }
        }

        return false;
    }
}
