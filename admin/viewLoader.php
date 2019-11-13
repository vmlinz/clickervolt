<?php

namespace ClickerVolt;

class ViewLoader
{
    static function dashboard()
    {
        self::view( 'dashboard' );
    }
    
    static function newLink()
    {
        self::view( 'newLink' );
    }
    
    static function trackingURL()
    {
        self::view( 'trackingURL' );
    }
    
    private static function view( $viewName )
    {
        self::addFeatures();
        echo  "<div class='clickervolt-view'>" ;
        include __DIR__ . "/views/{$viewName}.php";
        echo  "</div>" ;
    }
    
    private static function addFeatures()
    {
        
        if ( !(self::$flags & self::FLAG_FEATURES) ) {
            self::$flags |= self::FLAG_FEATURES;
            $features = [];
            $featuresJSON = json_encode( $features );
            echo  "<script>ClickerVoltFeatures = {$featuresJSON};</script>" ;
        }
    
    }
    
    private static  $flags = 0 ;
    const  FLAG_LIVECHAT = 0x1 ;
    const  FLAG_FEATURES = 0x2 ;
}