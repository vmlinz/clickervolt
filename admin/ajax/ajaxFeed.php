<?php

namespace ClickerVolt;

require_once __DIR__ . '/ajax.php';

class AjaxFeed extends Ajax
{

    const WP_OPTION_LATEST_NEWS_TIMESTAMP = 'clickervolt-latest-news-timestamp';

    static function loadRSS()
    {
        $url = Sanitizer::sanitizeURL($_POST['url']);
        $rss = fetch_feed($url);
        $feed = [
            'meta' => [
                'hasNewContent' => false
            ],
            'items' => []
        ];

        if (is_wp_error($rss)) {
            throw new \Exception($rss->get_error_message());
        }

        $maxItems = $rss->get_item_quantity(10);
        if ($maxItems > 0) {
            $rssItems = $rss->get_items(0, $maxItems);
            foreach ($rssItems as $rssItem) {
                $feed['items'][] = [
                    'url' => $rssItem->get_permalink(),
                    'title' => $rssItem->get_title(),
                    'description' => $rssItem->get_description(),
                    'timestamp' => $rssItem->get_date('U'),
                ];
            }

            $latestNewsTimestamp = $feed['items'][0]['timestamp'];
            $storedLatest = get_option(self::WP_OPTION_LATEST_NEWS_TIMESTAMP, 0);
            if ($storedLatest < $latestNewsTimestamp) {
                update_option(self::WP_OPTION_LATEST_NEWS_TIMESTAMP, $latestNewsTimestamp);
                if ($storedLatest) {
                    $feed['meta']['hasNewContent'] = true;
                }
            }
        }

        return $feed;
    }
};
