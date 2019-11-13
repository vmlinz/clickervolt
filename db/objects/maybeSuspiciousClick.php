<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/arraySerializer.php';
require_once __DIR__ . '/../../utils/fileTools.php';

class MaybeSuspiciousClick implements ArraySerializer
{
    use ArraySerializerImpl;

    const MAX_CANCELLING_DELAY = 10;
    const STORAGE_PATH = 'unprocessed_maybe_suspicious_clicks';

    private $timeCreated;
    private $clickId;

    function __construct($clickId)
    {
        $this->timeCreated = time();
        $this->clickId = $clickId;
    }

    function getTimeCreated()
    {
        return $this->timeCreated;
    }

    function getClickId()
    {
        return $this->clickId;
    }

    /**
     * 
     */
    function queue()
    {
        $toQueue = json_encode($this->toArray());
        FileTools::atomicSave(self::getPath($this->clickId), $toQueue);
    }

    static function getPath($clickId)
    {
        return FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$clickId}";
    }
}
