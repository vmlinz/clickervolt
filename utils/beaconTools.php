<?php

namespace ClickerVolt;

class BeaconTools
{
    static function beaconVarsToGET()
    {
        if (empty($_GET) && empty($_POST)) {
            parse_str(file_get_contents('php://input'), $beaconData);
            if (!empty($beaconData)) {
                require_once __DIR__ . '/urlTools.php';
                $_GET = array_merge($_GET, $beaconData);
                foreach ($beaconData as $v) {
                    $varQueryVars = URLTools::getParams($v, true);
                    if (!empty($varQueryVars)) {
                        $_GET = array_merge($varQueryVars, $_GET);
                    }
                }
                $_SERVER['REQUEST_URI'] = URLTools::appendQueryParams($_SERVER['REQUEST_URI'], $beaconData);
            }
        }
    }
}
