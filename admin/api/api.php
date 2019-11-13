<?php

namespace ClickerVolt;

class Api
{

    /**
     * @return \ClickerVolt\Api
     */
    static function singleton()
    {
        if (!self::$singleton) {
            self::$singleton = new static();
        }
        return self::$singleton;
    }

    /** 
     * 
     */
    static function registerRoutes()
    {
        self::singleton()->registerRoutesV1();
    }

    /**
     * 
     */
    function registerRoutesV1()
    {
        $namespace = "clickervolt/api/v1";

        register_rest_route($namespace, '/recaptchaVerify', [
            'methods' => 'POST',
            'callback' => [$this, 'recaptchaVerify'],
        ]);

        register_rest_route($namespace, '/updateMaxmindDBs', [
            'methods' => 'GET',
            'callback' => [$this, 'updateMaxmindDBs'],
        ]);
    }

    /**
     * 
     */
    function recaptchaVerify($request)
    {
        require_once __DIR__ . '/../../db/objects/cvSettings.php';
        require_once __DIR__ . '/../../utils/ipTools.php';
        require_once __DIR__ . '/../../utils/urlTools.php';

        $params = [
            'sslverify' => false,
            'body' => [
                'secret' => CVSettings::get(CVSettings::RECAPTCHA3_SECRET_KEY),
                'response' => $request->get_param('token'),
                'remoteip' => IPTools::getUserIP()
            ]
        ];
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $params);
        return $response;
    }

    /**
     * 
     */
    function updateMaxmindDBs($request)
    {
        require_once __DIR__ . '/../tools/maxmindDBDownloader.php';
        MaxmindDBDownloader::updateDBs();
    }

    protected function __construct()
    { }

    private static $singleton = null;
}
