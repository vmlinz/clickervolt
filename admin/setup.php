<?php

namespace ClickerVolt;

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/objects/cvSettings.php';
require_once __DIR__ . '/../redirect/router.php';
require_once __DIR__ . '/../redirect/jsTracking/jsTracking.php';
require_once __DIR__ . '/../utils/urlTools.php';
require_once __DIR__ . '/../utils/fileTools.php';
require_once __DIR__ . '/viewLoader.php';
require_once __DIR__ . '/reporting/drilldown.php';

class Setup
{
    /**
     * 
     */
    static function onActivate()
    {
        DB::singleton()->setupTables();
    }

    /**
     * 
     */
    static function onLoaded()
    {
        if (!defined('DOING_AJAX') && !defined('DOING_CRON')) {
            if (is_admin()) {
                DB::singleton()->setupTables();
                self::refreshCache();
            } else {
                // Front-end redirect...
                (new Router())->goToFinalURL();
            }
        }
    }

    /**
     * 
     */
    static function onDeactivate()
    {
        $crons = [
            'clickervolt_cron_clicks_queue',
            'clickervolt_cron_maxmind_update_dbs'
        ];

        foreach ($crons as $cron) {
            $timestamp = wp_next_scheduled($cron);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $cron);
            }
        }
    }

    /**
     * 
     */
    static function onDelete()
    { }

    static function refreshCache()
    {
        URLTools::getPluginURL(true);
        URLTools::getHomeURL(true);
        FileTools::getAbsPath(true);

        // Ensure that the remote tracking script is cached
        JSTracking::getRemoteTrackingScript('#SLUG#', ['minimize' => true, 'forceRecache' => true]);
    }

    /**
     * 
     */
    static function enqueueScripts($hook)
    {
        if ($hook == "toplevel_page_clickervolt") {
            $version = DB::VERSION;

            $clickerVoltBaseFolder = FileTools::getPluginFolderName();

            $jsFiles = [
                'clickervolt-functions.js' => "/{$clickerVoltBaseFolder}/admin/js/functions.js",
                'clickervolt-stats.js' => "/{$clickerVoltBaseFolder}/admin/js/stats.js",
                'clickervolt-modals.js' => "/{$clickerVoltBaseFolder}/admin/js/modals.js",
                'clickervolt-affiliate-networks.js' => "/{$clickerVoltBaseFolder}/admin/js/affiliate-networks.js",
                'clickervolt-validator-1.js' => "/{$clickerVoltBaseFolder}/admin/js/others/validator/jquery.validate.min.js",
                'clickervolt-validator-2.js' => "/{$clickerVoltBaseFolder}/admin/js/others/validator/additional-methods.min.js",
                'datatables-jquery' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/jquery.dataTables.min.js",
                'datatables-buttons' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/dataTables.buttons.min.js",
                'datatables-colVis' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/buttons.colVis.min.js",
                'datatables-html5' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/buttons.html5.min.js",
                'datatables-fixedColumns' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/dataTables.fixedColumns.min.js",
                'datatables-fixedHeader' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/dataTables.fixedHeader.min.js",
                'clickervolt-datatables-treegrid' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/datatables.treegrid.js",
                'clickervolt-maximize-select2' => "/{$clickerVoltBaseFolder}/admin/js/others/ux/maximize-select2-height.min.js",
                'select2' => "/{$clickerVoltBaseFolder}/admin/js/others/select2/select2.full.min.js",
                'jquery.dropdown' => "/{$clickerVoltBaseFolder}/admin/js/others/ux/jquery.dropdown.min.js",
                'moment.min.js' => "/{$clickerVoltBaseFolder}/admin/js/others/moment/moment.min.js",
                'daterangepicker.min.js' => "/{$clickerVoltBaseFolder}/admin/js/others/daterangepicker/daterangepicker.min.js",
                'jquery-confirm.min.js' => "/{$clickerVoltBaseFolder}/admin/js/others/jquery-confirm/jquery-confirm.min.js",
                'ace1.4.2.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/ace.js",
                'ace1.4.2-theme-monokai.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/theme-monokai.js",
                'ace1.4.2-mode-html.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/mode-html.js",
                'ace1.4.2-mode-php.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/mode-php.js",
                'ace1.4.2-worker-html.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/worker-html.js",
                'ace1.4.2-worker-php.js' => "/{$clickerVoltBaseFolder}/admin/js/others/ace/worker-php.js",
                'jquery-ui-accordion' => "",
            ];

            $cssFiles = [
                'clickervolt-styles.css' => "/{$clickerVoltBaseFolder}/admin/css/styles.css",
                'datatables-jquery' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/css/jquery.dataTables.min.css",
                'datatables-buttons' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/css/buttons.dataTables.min.css",
                'datatables-fixedColumns' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/css/fixedColumns.dataTables.min.css",
                'datatables-fixedHeader' => "/{$clickerVoltBaseFolder}/admin/js/others/datatables/css/fixedHeader.dataTables.min.css",
                'select2' => "/{$clickerVoltBaseFolder}/admin/js/others/select2/css/select2.min.css",
                'daterangepicker.css' => "/{$clickerVoltBaseFolder}/admin/js/others/daterangepicker/css/daterangepicker.css",
                'jquery-confirm.min.css' => "/{$clickerVoltBaseFolder}/admin/js/others/jquery-confirm/css/jquery-confirm.min.css",
            ];

            foreach ($jsFiles as $handle => $file) {
                wp_enqueue_script($handle, plugins_url() . "{$file}?v={$version}");
            }

            foreach ($cssFiles as $handle => $file) {
                wp_enqueue_style($handle, plugins_url() . "{$file}?v={$version}");
            }

            self::localizeJS();
        }
    }

    /**
     * 
     */
    static function localizeJS()
    {
        $clickerVoltVars = [
            'urls' => [
                'home' => get_site_url(),
                'plugin' => plugins_url() . '/clickervolt',
                'ajax' => admin_url('admin-ajax.php'),
            ],
            'clickervolt_nonce' => wp_create_nonce('clickervolt'),
            'is_mobile' => wp_is_mobile(),
            'const' => [
                'Router' => json_encode(self::get_class_consts('ClickerVolt\\Router')),
                'DynamicTokens' => json_encode(DynamicTokens::TOKENS),
                'DistributionTypes' => json_encode([
                    'RANDOM' => DistributionRandom::TYPE,
                    'SEQUENTIAL' => DistributionSequential::TYPE,
                    'AUTO_OPTIM' => DistributionAutoOptim::TYPE,
                ]),
                'CostTypes' => json_encode([
                    'CPC' => Link::COST_TYPE_CPC,
                    'CPA' => Link::COST_TYPE_CPA,
                    'Total' => Link::COST_TYPE_TOTAL,
                ]),
                'RedirectModes' => json_encode([
                    'PERMANENT' => Link::REDIRECT_MODE_PERMANENT,
                    'TEMPORARY' => Link::REDIRECT_MODE_TEMPORARY,
                    'DMR' => Link::REDIRECT_MODE_DMR,
                    'CLOAKING' => Link::REDIRECT_MODE_CLOAKING,
                    'VOLTIFY' => Link::REDIRECT_MODE_VOLTIFY,
                ]),
                'ReportTypes' => json_encode([
                    'LINKS_ALL_AGGREGATED' => \ClickerVolt\Reporting\Request::LINKS_ALL_AGGREGATED,
                    'LINKS_ALL_SEPARATED' => \ClickerVolt\Reporting\Request::LINKS_ALL_SEPARATED,
                ]),
                'TableSourceTemplates' => json_encode(self::get_class_consts('ClickerVolt\\TableSourceTemplates')),
                'TableLinks' => json_encode(self::get_class_consts('ClickerVolt\\TableLinks')),
                'ReportingSegments' => json_encode(self::get_class_consts('ClickerVolt\\Reporting\\Segment')),
                'AjaxStats' => json_encode(self::get_class_consts('ClickerVolt\\AjaxStats')),
                'HandlerWholePath' => json_encode(self::get_class_consts('ClickerVolt\\Reporting\\HandlerWholePath')),
                'RedirectRules' => json_encode(self::get_class_consts('ClickerVolt\\Rules')),
                'ConvPixelHTMLTemplate' => json_encode(PixelInfo::getPixelHTML('-CID-', '-TYPE-', '-NAME-', '-REV-', '-SLUG-')),
                'ConvPostbackURLTemplate' => json_encode(PixelInfo::getPostbackURL('-CID-', '-TYPE-', '-NAME-', '-REV-')),
                'CVSettings' => json_encode(self::get_class_consts('ClickerVolt\\CVSettings')),
            ],
            'settings' => [
                'recaptchaV3SiteKey' => CVSettings::get(CVSettings::RECAPTCHA3_SITE_KEY),
                'recaptchaV3SecretKey' => CVSettings::get(CVSettings::RECAPTCHA3_SECRET_KEY),
                'recaptchaV3HideBadge' => CVSettings::get(CVSettings::RECAPTCHA3_HIDE_BADGE),
                'ipDetectionType' => CVSettings::get(CVSettings::IP_DETECTION_TYPE),
                'permalinkStructure' => get_option('permalink_structure'),
            ],
        ];
        wp_localize_script('clickervolt-functions.js', 'clickerVoltVars', $clickerVoltVars);
    }

    /**
     * 
     */
    static function addMainMenu()
    {
        $mainSlug = 'clickervolt';

        add_menu_page(
            'ClickerVolt',
            'ClickerVolt',
            'manage_options',
            $mainSlug,
            ['\\ClickerVolt\\ViewLoader', 'dashboard'],
            plugin_dir_url(__FILE__) . 'images/icons/rt16.png',
            2
        );

        // add_submenu_page(
        //     $mainSlug,
        //     'New Link',
        //     'New Link',
        //     'manage_options',
        //     'new-link',
        //     [ '\\ClickerVolt\\ViewLoader', 'newLink' ]
        // );
    }

    /**
     * 
     */
    static function addFooterElements()
    {
        // echo "<p><a href='#'>ClickerVolt Console</a></p>";
    }

    /**
     * 
     */
    static private function get_class_consts($className)
    {
        $c = new \ReflectionClass($className);
        return ($c->getConstants());
    }
}
