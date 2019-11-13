<?php

namespace ClickerVolt;

require_once __DIR__ . '/redirector.php';

class Redirector302 implements Redirector
{

    /**
     * 
     */
    function redirectTo($url, $options = [])
    {
        header('Content-type: text/html; charset=utf-8', true);
        header('Cache-control: no-cache, must-revalidate', true);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT', true);
        header("location: {$url}", true, 302);
        exit;
    }
}
