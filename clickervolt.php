<?php

/**
 * Plugin Name: ClickerVolt
 * Plugin URI:  https://clickervolt.com/
 * Description: Advanced click tracking, link cloaking and affiliate campaigns management made easy.
 * Version:     1.145
 * Author:      ClickerVolt.com
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 */

namespace ClickerVolt;

// TODO: fix bug -> CPA cost is divided on child rows instead of recomputed based on conversions count
// TODO: add pagination in reports
// TODO: fix bug -> deleted link still shows as number
// TODO: add external cron option
// TODO: check csv export on Mac, os Sierra
// TODO: no single ip extraction possible right now, only ranges
// TODO: conversion pixels vars editor: add field for cid
// TODO: Change CookieTools::isLoggedIn to check for our own cookie (to be created) instead of wordpress's one. Because if a customer has wordpress users, then the clicks from his users won't be tracked.
// TODO: url encode characters entered in the source variables in editor - for example "/" cannot be used in any V as it breaks the url.
// TODO: add pause time to time tracking (when tab not visible)
// TODO: for future traffic source auto-optim, check if these sources have API: propellerads, popads, popcash, plugrush

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('\ClickerVolt\cli_fs')) {
    cli_fs()->set_basename(true, __FILE__);
    return;
}

require_once __DIR__ . '/freemiusSetup.php';
require_once __DIR__ . '/admin/setup.php';
require_once __DIR__ . '/admin/cron.php';
require_once __DIR__ . '/admin/ajax/ajaxLinks.php';
require_once __DIR__ . '/admin/ajax/ajaxSources.php';
require_once __DIR__ . '/admin/ajax/ajaxStats.php';
require_once __DIR__ . '/admin/ajax/ajaxSearches.php';
require_once __DIR__ . '/admin/ajax/ajaxFeed.php';
require_once __DIR__ . '/admin/ajax/ajaxCVSettings.php';
require_once __DIR__ . '/admin/reporting/segment.php';
require_once __DIR__ . '/admin/reporting/handlers/handlerWholePath.php';
require_once __DIR__ . '/admin/api/api.php';
require_once __DIR__ . '/redirect/dynamicTokens.php';
require_once __DIR__ . '/pixel/pixelInfo.php';
require_once __DIR__ . '/redirect/router.php';
require_once __DIR__ . '/redirect/jsTracking/jsTracking.php';
require_once __DIR__ . '/redirect/rules/rules.php';
require_once __DIR__ . '/db/tableSourceTemplates.php';
require_once __DIR__ . '/utils/dataProxy.php';

add_action('plugins_loaded', ['ClickerVolt\\Setup', 'onLoaded']);
add_action('rest_api_init', ['ClickerVolt\\Api', 'registerRoutes']);

add_action('rest_api_init', function () {
    // We want to register 'wp_unique_post_slug' early to be able to intercept post inserts/updates
    add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
        $link = DataProxy::getLink($slug);
        if ($link != null) {
            if ($post_status != AjaxLinks::STATUS_SLUG_CHECKING || $link->getId() != $post_ID) {
                // A link with this slug already exists... can't create post with that one.
                // Generate a random slug instead.
                $slug .= "-" . time();
            }
        }
        return $slug;
    }, 10, 6);
});

if (is_admin() || defined('DOING_AJAX') || defined('DOING_CRON')) {

    register_activation_hook(__FILE__, ['ClickerVolt\\Setup', 'onActivate']);
    register_deactivation_hook(__FILE__, ['ClickerVolt\\Setup', 'onDeactivate']);
    register_uninstall_hook(__FILE__, ['ClickerVolt\\Setup', 'onDelete']);

    add_action('admin_enqueue_scripts', ['ClickerVolt\\Setup', 'enqueueScripts']);
    add_action('admin_menu', ['ClickerVolt\\Setup', 'addMainMenu']);
    add_action('in_admin_footer', ['ClickerVolt\\Setup', 'addFooterElements']);

    // Ajax actions

    add_action('wp_ajax_clickervolt_save_link', ['ClickerVolt\\AjaxLinks', 'saveLinkAjax']);
    add_action('wp_ajax_clickervolt_get_link', ['ClickerVolt\\AjaxLinks', 'getLinkAjax']);
    add_action('wp_ajax_clickervolt_get_link_by_slug', ['ClickerVolt\\AjaxLinks', 'getLinkBySlugAjax']);
    add_action('wp_ajax_clickervolt_delete_link_by_slug', ['ClickerVolt\\AjaxLinks', 'deleteLinkBySlugAjax']);
    add_action('wp_ajax_clickervolt_get_all_slugs', ['ClickerVolt\\AjaxLinks', 'getAllSlugsAjax']);
    add_action('wp_ajax_clickervolt_get_aida_script_template', ['ClickerVolt\\AjaxLinks', 'getAIDAScriptTemplateAjax']);
    add_action('wp_ajax_clickervolt_save_source_template', ['ClickerVolt\\AjaxSources', 'saveSourceAjax']);
    add_action('wp_ajax_clickervolt_get_sources', ['ClickerVolt\\AjaxSources', 'getAllSourcesAjax']);
    add_action('wp_ajax_clickervolt_delete_source_template', ['ClickerVolt\\AjaxSources', 'deleteSourceAjax']);
    add_action('wp_ajax_clickervolt_process_clicks_queue', ['ClickerVolt\\AjaxStats', 'processClicksQueueAjax']);
    add_action('wp_ajax_clickervolt_get_stats', ['ClickerVolt\\AjaxStats', 'getStatsAjax']);
    add_action('wp_ajax_clickervolt_get_clicklog', ['ClickerVolt\\AjaxStats', 'getClickLogAjax']);
    add_action('wp_ajax_clickervolt_search_isps', ['ClickerVolt\\AjaxSearches', 'searchISPsAjax']);
    add_action('wp_ajax_clickervolt_search_regions', ['ClickerVolt\\AjaxSearches', 'searchRegionsAjax']);
    add_action('wp_ajax_clickervolt_search_cities', ['ClickerVolt\\AjaxSearches', 'searchCitiesAjax']);
    add_action('wp_ajax_clickervolt_search_device_names', ['ClickerVolt\\AjaxSearches', 'searchDeviceNamesAjax']);
    add_action('wp_ajax_clickervolt_save_settings', ['ClickerVolt\\AjaxCVSettings', 'saveAjax']);
    add_action('wp_ajax_clickervolt_load_rss', ['ClickerVolt\\AjaxFeed', 'loadRSSAjax']);

    // Crons

    add_filter('cron_schedules', function ($schedules) {

        $schedules['clickervolt_one_minute'] = array(
            'interval' => 60,
            'display'  => esc_html__('Each Minute'),
        );

        $schedules['clickervolt_once_per_week'] = array(
            'interval' => 60 * 60 * 24 * 7,
            'display'  => esc_html__('Once per Week'),
        );

        return $schedules;
    });

    add_action('clickervolt_cron_clicks_queue', ['ClickerVolt\\Cron', 'processClicksQueue']);
    if (!wp_next_scheduled('clickervolt_cron_clicks_queue')) {
        wp_schedule_event(time(), 'clickervolt_one_minute', 'clickervolt_cron_clicks_queue');
    }

    add_action('clickervolt_cron_maxmind_update_dbs', ['ClickerVolt\\Cron', 'maxmindUpdate']);
    if (!wp_next_scheduled('clickervolt_cron_maxmind_update_dbs')) {
        wp_schedule_event(time(), 'clickervolt_once_per_week', 'clickervolt_cron_maxmind_update_dbs');
    }
}
