<?php

namespace ClickerVolt;
require_once __DIR__ . '/../../others/spyc/Spyc.php';

$pathDeviceNames = __DIR__ . '/../../others/device-detector/device-names.txt';
$pathDeviceBrands = __DIR__ . '/../../others/device-detector/device-brands.txt';

$names = [];
$brands = [];
$data = \Spyc::YAMLLoad( __DIR__ . '/../../others/device-detector/regexes/device/mobiles.yml' );
foreach( $data as $brand => $content ) {
    $brands[] = strtolower($brand);
    if( empty($content['models']) && !empty($content['model']) ) {
        $content['models'] = [ 
            [ 'model' => $content['model'] ]
        ];
    }
    if( !empty($content['models']) ) {
        foreach( $content['models'] as $model ) {
            if( !empty($model['model']) && strpos($model['model'], '$') === false ) {
                $names[] = strtolower($brand) . ' ' . strtolower($model['model']);     
            }
        }
    }
}

$names = array_unique( $names );
sort($names);
file_put_contents($pathDeviceNames, implode(PHP_EOL, $names));

$brands = array_unique( $brands );
sort($brands);
file_put_contents($pathDeviceBrands, implode(PHP_EOL, $brands));