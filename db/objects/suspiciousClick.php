<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/arraySerializer.php';
require_once __DIR__ . '/../../utils/fileTools.php';

class SuspiciousClick implements ArraySerializer
{
    use ArraySerializerImpl;

    const STORAGE_PATH = 'unprocessed_suspicious_clicks';

    private $clickId;
    private $score;

    function __construct($clickId, $score)
    {
        $this->clickId = $clickId;
        $this->score = $score;
    }

    function getClickId()
    {
        return $this->clickId;
    }

    function setClickId($clickId)
    {
        $this->clickId = $clickId;
    }

    /**
     * 
     */
    function queue()
    {
        $toQueue = json_encode($this->toArray());

        $file = implode('_', [
            $this->clickId,
        ]);

        $path = FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$file}";
        FileTools::atomicSave($path, $toQueue);
    }
}
