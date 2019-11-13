<?php

namespace ClickerVolt\Reporting;

require_once __DIR__ . '/handlerBase.php';
require_once __DIR__ . '/../../../utils/deviceDetection.php';

class HandlerWholePathDevices extends HandlerWholePath
{

    /**
     * 
     * @return [] (first element is for 'select from', others are for joins - the ones for joins must include the join clause)
     */
    protected function getTableNames($request)
    {

        $table = new \ClickerVolt\TableStatsWholePathDevices();
        $tableName = $table->getName();

        $tableDevices = new \ClickerVolt\TableDevices();

        $tables = [$tableName, $tableDevices->getName() . " as devices on devices.deviceHash = {$tableName}.deviceHash"];
        return $this->addPathsTables($request, $tableName, $tables);
    }

    /**
     * 
     */
    public function formatValue($value, $segmentType)
    {

        switch ($segmentType) {

            case Segment::TYPE_DEVICE_TYPE:
                if (!empty(\ClickerVolt\DeviceDetection::DEVICE_TYPES[$value])) {
                    $value = \ClickerVolt\DeviceDetection::DEVICE_TYPES[$value];
                }
                break;

            default:
                $value = parent::formatValue($value, $segmentType);
                break;
        }

        return $value;
    }

    /**
     * 
     * @return []
     */
    public function getMapper($request)
    {

        $mapper = [

            Segment::TYPE_DEVICE_TYPE => [
                self::MAP_SELECT => 'devices.deviceType'
            ],

            Segment::TYPE_DEVICE_BRAND => [
                self::MAP_SELECT => 'devices.deviceBrand'
            ],

            Segment::TYPE_DEVICE_NAME => [
                self::MAP_SELECT => 'devices.deviceName'
            ],

            Segment::TYPE_DEVICE_OS => [
                self::MAP_SELECT => 'devices.deviceOs'
            ],

            Segment::TYPE_DEVICE_OS_VERSION => [
                self::MAP_SELECT => 'concat_ws(" ", devices.deviceOs, devices.deviceOsVersion)'
            ],

            Segment::TYPE_DEVICE_BROWSER => [
                self::MAP_SELECT => 'devices.deviceBrowser'
            ],

            Segment::TYPE_DEVICE_BROWSER_VERSION => [
                self::MAP_SELECT => 'concat_ws(" ", devices.deviceBrowser, devices.deviceBrowserVersion)'
            ],

            Segment::TYPE_LANGUAGE => [
                self::MAP_SELECT => 'devices.deviceLanguage'
            ],
        ];

        return array_merge(parent::getMapper($request), $mapper);
    }
}
