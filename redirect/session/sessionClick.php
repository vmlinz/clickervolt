<?php

namespace ClickerVolt;

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../utils/arraySerializer.php';

class SessionClickInfo implements ArraySerializer
{
    use ArraySerializerImpl;

    private $clickId;
    private $timestamp;
    private $slug;
    private $source;
    private $v1;
    private $v2;
    private $v3;
    private $v4;
    private $v5;
    private $v6;
    private $v7;
    private $v8;
    private $v9;
    private $v10;
    private $externalId;
    private $organic;

    function __construct($properties = [])
    {
        $this->fromArray($properties);
    }

    function getClickId()
    {
        return $this->clickId;
    }

    function getTimestamp()
    {
        return $this->timestamp;
    }

    function getSlug()
    {
        return $this->slug;
    }

    function getSource()
    {
        return $this->source;
    }

    function getV1()
    {
        return $this->v1;
    }

    function getV2()
    {
        return $this->v2;
    }

    function getV3()
    {
        return $this->v3;
    }

    function getV4()
    {
        return $this->v4;
    }

    function getV5()
    {
        return $this->v5;
    }

    function getV6()
    {
        return $this->v6;
    }

    function getV7()
    {
        return $this->v7;
    }

    function getV8()
    {
        return $this->v8;
    }

    function getV9()
    {
        return $this->v9;
    }

    function getV10()
    {
        return $this->v10;
    }

    function getExternalId()
    {
        return $this->externalId;
    }

    function isOrganic()
    {
        return $this->organic ? true : false;
    }
}

class SessionClick extends Session
{
    /**
     * @param \ClickerVolt\SessionClickInfo $clickInfo
     */
    public function setClickInfo($clickInfo)
    {
        $clickData = $clickInfo->toArray();
        $this->set($this->getKey($clickInfo->getClickId()), json_encode($clickData));
        $this->set($this->getKey(), json_encode($clickData));
        $this->set($this->getSlugKey($clickInfo->getSlug()), $clickInfo->getClickId());
    }

    /**
     * @return \ClickerVolt\SessionClickInfo
     */
    public function getClickInfo($clickId = null)
    {
        $clickInfo = $this->get($this->getKey($clickId));
        if ($clickInfo) {
            $clickInfo = new SessionClickInfo(json_decode($clickInfo, true));
        }

        return $clickInfo;
    }

    /**
     * 
     */
    public function getLatestClickId($slug)
    {
        return $this->get($this->getSlugKey($slug));
    }

    private function getKey($clickId = null)
    {
        return $clickId ? "info-{$clickId}" : "info-latest-click";
    }

    private function getSlugKey($slug)
    {
        return "info-{$slug}";
    }
}
