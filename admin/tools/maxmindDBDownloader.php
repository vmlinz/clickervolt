<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/fileTools.php';
require_once ABSPATH . 'wp-admin/includes/file.php';

class MaxmindDBDownloader
{
    /**
     * @throws \Exception
     */
    static function updateDBs()
    {

        $urls = [
            "asn.mmdb" => "http://clickervolt.com/files/maxminddbs/asn.mmdb",
            "city.mmdb" => "http://clickervolt.com/files/maxminddbs/city.mmdb",
            "asn-list.txt" => "http://clickervolt.com/files/maxminddbs/asn-list.txt",
            "city-list.txt" => "http://clickervolt.com/files/maxminddbs/city-list.txt",
            "region-list.txt" => "http://clickervolt.com/files/maxminddbs/region-list.txt"
        ];

        $responses = [];

        try {

            $timeout = 60 * 15;

            foreach ($urls as $k => $url) {
                $response = download_url($url, $timeout);
                if (is_wp_error($response)) {
                    throw new \Exception($response->get_error_message());
                }
                $responses[$k] = $response;
            }

            $targetDir = FileTools::getDataFolderPath("maxmind_dbs");
            foreach ($responses as $k => $path) {
                rename($path, "{$targetDir}/{$k}");
            }
        } catch (\Exception $ex) {

            foreach ($responses as $k => $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            throw $ex;
        }
    }
}
