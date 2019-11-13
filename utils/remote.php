<?php

namespace ClickerVolt;

class Remote
{
    const OPTION_USE_COOKIES = 'cookies';

    /**
     * 
     */
    static function singleton()
    {
        if (!self::$singleton) {
            self::$singleton = new static();
        }
        return self::$singleton;
    }

    /**
     * @return array [string, string] [Raw Response, Final URL] (in case of redirections)
     * @throws \Exception
     */
    function get($url, $options = [])
    {
        return $this->request($url, [], 'GET', $options);
    }

    /**
     * @return array [string, string] [Raw Response, Final URL] (in case of redirections)
     * @throws \Exception
     */
    function post($url, $params = [])
    {
        return $this->request($url, $params, 'POST');
    }

    /**
     * 
     * @return array [string, string] [Raw Response, Final URL] (in case of redirections)
     * @throws \Exception
     */
    protected function request($url, $params = [], $verb = 'GET', $options = [])
    {
        $defaultOptions = [
            self::OPTION_USE_COOKIES => false,
        ];
        $options = array_merge($defaultOptions, $options);

        require_once __DIR__ . '/fileTools.php';

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_MAXREDIRS, 50);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_TIMEOUT, 10);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);

        if ($verb == 'POST') {
            curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($c, CURLOPT_POST, 1);
        } else {
            if ($options[self::OPTION_USE_COOKIES]) {
                $cookieFile = FileTools::getDataFolderPath('remote/cookies') . DIRECTORY_SEPARATOR . md5(URLTools::getHost($url));
                curl_setopt($c, CURLOPT_COOKIESESSION, true);
                curl_setopt($c, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($c, CURLOPT_COOKIEFILE, $cookieFile);
            }

            curl_setopt($c, CURLINFO_HEADER_OUT, true);
            curl_setopt($c, CURLOPT_ENCODING, 'gzip');
            curl_setopt($c, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
                'Cache-Control: max-age=0',
                'Connection: keep-alive',
                'Keep-Alive: 300',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Accept-Language: en-us,en;q=0.5',
                'Pragma: ',
            ]);
        }

        $response = curl_exec($c);
        $info = curl_getinfo($c);

        if ($response === false || $info['http_code'] != 200) {
            $error = "CURL Error [" . $info['http_code'] . "]: " . curl_error($c);
        }

        curl_close($c);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        return [$response, $info['url']];
    }

    protected function __construct()
    { }

    static private $singleton = null;
}
