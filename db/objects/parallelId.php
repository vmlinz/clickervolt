<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/arraySerializer.php';
require_once __DIR__ . '/../../utils/fileTools.php';

class ParallelId implements ArraySerializer
{
    use ArraySerializerImpl;

    const MAX_PID_LENGTH = 255;
    const MAX_PAIRING_DELAY = 3;
    const STORAGE_PATH = 'unprocessed_parallelids';

    static $urlTriggerVars = [
        'gclid',
    ];

    private $parallelId;
    private $clickId;

    // Properties needed for some processing, but that are not stored in db as part of the click
    private $paired;
    private $timeCreated;
    private $clickData;

    /**
     * @param $params array
     * @param $mergeToExisting bool
     */
    function __construct($params, $mergeToExisting = false)
    {
        $this->timeCreated = time();
        $this->fromArray($params);

        if ($mergeToExisting) {
            if (!empty($params['clickId']) && !empty($params['clickData'])) {
                $path = self::getPathFor($this->parallelId);
                if (file_exists($path)) {
                    $content = FileTools::atomicLoad($path);
                    if ($content) {
                        $other = json_decode($content, true);
                        if ($other && !empty($other['clickData'])) {
                            $params['timeCreated'] = $other['timeCreated'];

                            if ($other['clickId'] != $params['clickId']) {
                                require_once __DIR__ . '/../tableClicks.php';
                                $paramsVarsCount = 0;
                                $otherVarsCount = 0;
                                foreach (Click::$colVars as $varName) {
                                    if (!empty($params['clickData'][$varName])) {
                                        $paramsVarsCount++;
                                    }
                                    if (!empty($other['clickData'][$varName])) {
                                        $otherVarsCount++;
                                        if (empty($params['clickData'][$varName])) {
                                            $params['clickData'][$varName] = $other['clickData'][$varName];
                                        }
                                    }
                                }
                                if ($otherVarsCount < $paramsVarsCount) {
                                    $params['clickId'] = $other['clickId'];
                                    $params['clickData']['id'] = $other['clickData']['id'];
                                }
                                $params['paired'] = true;
                                $this->fromArray($params);
                            }
                        }
                    }
                }
            }
        }
    }

    function getClickId()
    {
        return $this->clickId;
    }

    function getTimeCreated()
    {
        return $this->timeCreated;
    }

    /**
     * 
     */
    function queue()
    {
        $toQueue = json_encode($this->toArray());

        $path = self::getPathFor($this->parallelId);
        FileTools::atomicSave($path, $toQueue);
    }

    function unqueue()
    {
        $path = self::getPathFor($this->parallelId);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * 
     */
    static function getPathFor($parallelId)
    {
        $file = md5($parallelId);
        $path = FileTools::getDataFolderPath(self::STORAGE_PATH) . "/{$file}";
        return $path;
    }

    /**
     * @param $url string
     * @return \ClickerVolt\ParallelIdParam or null
     */
    static function fromURL($url = null)
    {
        require_once __DIR__ . '/../../utils/urlTools.php';
        if (!$url) {
            $url = URLTools::getCurrentURL();
        }
        return self::fromParams(URLTools::getParams($url, true));
    }

    /**
     * @param $params array
     * @return \ClickerVolt\ParallelIdParam or null
     */
    static function fromParams($params)
    {
        if (!empty($params)) {
            foreach (self::$urlTriggerVars as $var) {
                if (!empty($params[$var]) && strlen($params[$var]) <= self::MAX_PID_LENGTH) {
                    return new ParallelIdParam($var, $params[$var]);
                }
            }
        }
        return null;
    }

    /**
     * @param $url string
     * @param $params array
     * @return \ClickerVolt\ParallelIdParam or null
     */
    static function fromURLOrParams($url = null, $params = null)
    {
        $pid = self::fromURL($url);
        if (!$pid) {
            return self::fromParams($params);
        }
        return $pid;
    }
}

class ParallelIdParam
{
    private $key;
    private $value;

    function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    function getKey()
    {
        return $this->key;
    }

    function getValue()
    {
        return $this->value;
    }
}
