<?php

namespace ClickerVolt;

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../utils/arraySerializer.php';

class SessionSlug extends Session
{
    /**
     * 
     * @param string $slug
     * @param string $url
     */
    public function addVisitedSlugURL($slug, $url)
    {
        $visited = $this->get(self::KEY_VISITED_SLUGS);
        if ($visited) {
            $visited = json_decode($visited, true);
        } else {
            $visited = [];
        }

        if (empty($visited[$slug])) {
            $visited[$slug] = [];
        }

        if (!in_array($url, $visited[$slug])) {
            $visited[$slug][] = $url;
        }

        $this->set(self::KEY_VISITED_SLUGS, json_encode($visited));
    }

    /**
     * 
     * @param string $slug
     * @param string $url
     * @return bool
     */
    public function hasVisitedSlugURL($slug, $url)
    {
        $visited = $this->get(self::KEY_VISITED_SLUGS);
        if ($visited) {
            $visited = json_decode($visited, true);

            if (!empty($visited[$slug])) {
                return in_array($url, $visited[$slug]);
            }
        }

        return false;
    }

    const KEY_VISITED_SLUGS = "visited-slugs";
}
