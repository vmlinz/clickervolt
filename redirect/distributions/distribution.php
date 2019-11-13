<?php

namespace ClickerVolt;

abstract class Distribution
{

    /**
     * Must be called by all existing distribution implementations 
     */
    static function registerDistribution($type, $className)
    {

        self::$typeToClass[$type] = $className;
    }

    /**
     * @throws \Exception if type not registered
     */
    static function getDistribution($type)
    {

        if (empty(self::$typeToClass[$type])) {
            throw new \Exception("Distribution type '{$type}' has not been registered.");
        }

        if (empty(self::$typeToInstance[$type])) {

            $className = self::$typeToClass[$type];

            $parts = explode('\\', $className);
            $classNameWithoutNamespace = $parts[count($parts) - 1];

            $file = __DIR__ . '/' . lcfirst($classNameWithoutNamespace) . '.php';
            if (!file_exists($file)) {
                throw new \Exception("Distribution file for class '{$className}' not found.");
            }

            self::$typeToInstance[$type] = new $className;
        }

        return self::$typeToInstance[$type];
    }

    abstract function getType();
    abstract function getFinalURL($urls, $slug, $options = []);

    private static $typeToClass = [];
    private static $typeToInstance = [];
}
