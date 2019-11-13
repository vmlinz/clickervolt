<?php

namespace ClickerVolt;

require_once __DIR__ . '/fileTools.php';

class Logger
{

    const TYPE_ERROR = 'errors.txt';
    const TYPE_GENERAL = 'general.txt';

    static function getErrorLogger()
    {
        return self::getLogger(self::TYPE_ERROR);
    }

    static function getGeneralLogger()
    {
        return self::getLogger(self::TYPE_GENERAL);
    }

    function log($message)
    {
        $data = date("Y-m-d H:i:s") . " - {$message}" . PHP_EOL . PHP_EOL;
        file_put_contents($this->getFilePath(), $data, FILE_APPEND | LOCK_EX);
    }

    private function __construct($type)
    {
        $this->type = $type;
    }

    static private function getLogger($type)
    {
        if (!isset(self::$loggers[$type])) {
            self::$loggers[$type] = new self($type);
        }
        return self::$loggers[$type];
    }

    private function getFilePath()
    {
        return FileTools::getDataFolderPath('logs') . "/{$this->type}";
    }

    private $type;
    static private $loggers = [];
}
