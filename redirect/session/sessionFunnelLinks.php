<?php

namespace ClickerVolt;

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../utils/arraySerializer.php';

class SessionFunnelLinks extends Session
{
    /**
     * 
     */
    public function setFunnelLinks($linkId, $funnelLinkIds, $clickId, $url)
    {
        if ($funnelLinkIds) {
            foreach ($funnelLinkIds as $flid) {
                $key = $this->getKey($flid);
                $this->set($key, "{$clickId}#{$linkId}#{$url}");
            }
        }
    }

    /**
     * @param string $forLinkId
     * @return array [
     *   0 => string $parentClickId
     *   1 => string $parentLinkId
     *   2 => string $parentURL
     * ]
     */
    public function getParentInfo($forLinkId)
    {
        $key = $this->getKey($forLinkId);
        $val = $this->get($key);
        if ($val) {
            $response = explode('#', $val);
        } else {
            $response = [null, null, null];
        }

        return $response;
    }

    /**
     * 
     * @param string $toLinkId
     * @param string $url
     * 
     * @return array [
     *   0 => array $linkIdsPath
     *   1 => array $urlsPath
     * ]
     */
    public function getPathsTo($toLinkId, $toURL)
    {
        $linkIds = [
            $toLinkId
        ];

        $urls = [
            $toURL
        ];

        do {
            list($parentClickId, $parentLinkId, $parentURL) = $this->getParentInfo($toLinkId);
            if ($parentLinkId && empty($linkIds[$parentLinkId])) {
                $linkIds[$parentLinkId] = $parentLinkId;
                $urls[$parentLinkId] = $parentURL;
                $toLinkId = $parentLinkId;
            }
        } while ($parentLinkId);

        return [
            array_reverse(array_values($linkIds)),
            array_reverse(array_values($urls))
        ];
    }

    protected function getKey($funnelLinkId)
    {
        return "funlid5-{$funnelLinkId}";
    }
}
