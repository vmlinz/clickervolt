<?php

namespace ClickerVolt;

require_once __DIR__ . '/rules.php';

class RulesRouter
{

    /**
     * @param array $rules
     */
    function getFirstFulfilled($rules)
    {
        $fulfilledRule = null;

        foreach ($rules as $rule) {

            $allConditionsFulfilled = true;

            foreach ($rule['conditions'] as $condition) {
                $type = $condition['type'];
                $operator = $condition['operator'];
                $values = empty($condition['values']) ? [] : $condition['values'];

                foreach ($values as $k => $value) {
                    $parts = explode(Rules::VALUE_SEPARATOR, $value);
                    $values[$k] = $parts[count($parts) - 1];
                }

                switch ($type) {

                    case Rules::RULE_TYPE_COUNTRY: {
                            $allConditionsFulfilled &= $this->testGeo('getCountryCode', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_REGION: {
                            $allConditionsFulfilled &= $this->testGeo('getRegion', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_CITY: {
                            $allConditionsFulfilled &= $this->testGeo('getCity', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_ISP: {
                            $allConditionsFulfilled &= $this->testGeo('getIsp', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_IP: {
                            $allConditionsFulfilled &= $this->testIP($operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_LANGUAGE: {
                            $allConditionsFulfilled &= $this->testLanguage($operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_USER_AGENT: {
                            $allConditionsFulfilled &= $this->testUA($operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_DEVICE_TYPE: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceType', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_DEVICE_BRAND: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceBrand', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_DEVICE_NAME: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceName', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_OS: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceOs', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_OS_VERSION: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceOsVersion', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_BROWSER: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceBrowser', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_BROWSER_VERSION: {
                            $allConditionsFulfilled &= $this->testDevice('getDeviceBrowserVersion', $operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_DATE: {
                            $allConditionsFulfilled &= $this->testDate($operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_URL: {
                            $allConditionsFulfilled &= $this->testURL($operator, $values);
                        }
                        break;

                    case Rules::RULE_TYPE_REFERRER: {
                            $allConditionsFulfilled &= $this->testReferrer($operator, $values);
                        }
                        break;

                    default:
                        throw new \Exception("Unexpected rule type '{$type}'");
                }

                if (!$allConditionsFulfilled) {
                    break;
                }
            }

            if ($allConditionsFulfilled) {
                $fulfilledRule = $rule;
                break;
            }
        }

        return $fulfilledRule;
    }

    /**
     * 
     */
    protected function testReferrer($operator, $values)
    {
        $ref = empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER'];
        return $this->test($ref, $operator, $values);
    }

    /**
     * 
     */
    protected function testURL($operator, $values)
    {
        require_once __DIR__ . '/../../utils/urlTools.php';
        $url = URLTools::getCurrentURL();
        return $this->test($url, $operator, $values);
    }

    /**
     * 
     */
    protected function testDate($operator, $values)
    {
        $curDate = strtotime(date("Y-m-d") . ' 00:00:00');
        foreach ($values as $k => $value) {
            $values[$k] = strtotime("{$value} 00:00:00");
        }
        return $this->test($curDate, $operator, $values);
    }

    /**
     * 
     */
    protected function testUA($operator, $values)
    {
        $ua = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
        return $this->test($ua, $operator, $values);
    }

    /**
     * 
     */
    protected function testLanguage($operator, $values)
    {
        require_once __DIR__ . '/../../utils/languageCodes.php';

        $language = LanguageCodes::getBrowserLanguage();
        if (!empty($language)) {
            $parts = explode('-', $language);
            $language = $parts[0];
        }

        return $this->test($language, $operator, $values);
    }

    /**
     * 
     */
    protected function testIP($operator, $values)
    {
        require_once __DIR__ . '/../../utils/ipTools.php';

        $ip = IPTools::getUserIP();
        if ($operator == Rules::OPERATOR_EMPTY) {
            return empty($ip);
        } else if ($operator == Rules::OPERATOR_EMPTY_NOT) {
            return !empty($ip);
        }

        foreach ($values as $value) {
            if (IPTools::isInRange($ip, $value)) {
                if ($operator == Rules::OPERATOR_IS) {
                    return true;
                } else if ($operator == Rules::OPERATOR_IS_NOT) {
                    return false;
                }
            }
        }

        // If operator is "is not", then we come here if the ip isn't in any of the tested ranges.
        // If operator is "is", then we come here if the ip isn't in any of the tested ranges.
        // For "is-not", this means the test passes
        // For "is", this means the test fails
        return $operator == Rules::OPERATOR_IS_NOT;
    }

    /**
     * @return bool
     */
    protected function testGeo($geoIpMethodName, $operator, $values)
    {
        require_once __DIR__ . '/../../utils/geoIP.php';
        require_once __DIR__ . '/../../utils/ipTools.php';

        $geoIP = new GeoIP();
        $geoIP->resolve(IPTools::getUserIP());
        $location = call_user_func([$geoIP, $geoIpMethodName]);

        return $this->test($location, $operator, $values);
    }

    /**
     * @return bool
     */
    protected function testDevice($deviceDetectionMethodName, $operator, $values)
    {
        require_once __DIR__ . '/../../utils/deviceDetection.php';

        $ua = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];

        $dd = new DeviceDetection();
        $dd->resolve($ua);
        $data = call_user_func([$dd, $deviceDetectionMethodName]);

        return $this->test($data, $operator, $values);
    }

    /**
     * @return bool
     */
    protected function test($currentValue, $operator, $acceptedValues)
    {
        require_once __DIR__ . '/../../utils/stringTools.php';

        if (!$acceptedValues) {
            $acceptedValues = [];
        }

        switch ($operator) {
            case Rules::OPERATOR_EMPTY: {
                    if (empty($currentValue)) {
                        return true;
                    }
                }
                break;

            case Rules::OPERATOR_EMPTY_NOT: {
                    if (!empty($currentValue)) {
                        return true;
                    }
                }
                break;

            case Rules::OPERATOR_IS: {
                    foreach ($acceptedValues as $acceptedValue) {
                        if (StringTools::toLower($currentValue) == StringTools::toLower($acceptedValue)) {
                            return true;
                        }
                    }
                }
                break;

            case Rules::OPERATOR_IS_NOT: {
                    foreach ($acceptedValues as $acceptedValue) {
                        if (StringTools::toLower($currentValue) == StringTools::toLower($acceptedValue)) {
                            return false;
                        }
                    }
                    return true;
                }

            case Rules::OPERATOR_CONTAINS: {
                    foreach ($acceptedValues as $acceptedValue) {
                        if (stripos($currentValue, $acceptedValue) !== false) {
                            return true;
                        }
                    }
                }
                break;

            case Rules::OPERATOR_CONTAINS_NOT: {
                    foreach ($acceptedValues as $acceptedValue) {
                        if (stripos($currentValue, $acceptedValue) !== false) {
                            return false;
                        }
                    }
                    return true;
                }

            case Rules::OPERATOR_GREATER_THAN: {
                    if (is_numeric($currentValue)) {
                        foreach ($acceptedValues as $acceptedValue) {
                            if (is_numeric($acceptedValue) && $currentValue <= $acceptedValue) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
                break;

            case Rules::OPERATOR_GREATER_THAN_OR_EQUAL: {
                    if (is_numeric($currentValue)) {
                        foreach ($acceptedValues as $acceptedValue) {
                            if (is_numeric($acceptedValue) && $currentValue < $acceptedValue) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
                break;

            case Rules::OPERATOR_LESS_THAN: {
                    if (is_numeric($currentValue)) {
                        foreach ($acceptedValues as $acceptedValue) {
                            if (is_numeric($acceptedValue) && $currentValue >= $acceptedValue) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
                break;

            case Rules::OPERATOR_LESS_THAN_OR_EQUAL: {
                    if (is_numeric($currentValue)) {
                        foreach ($acceptedValues as $acceptedValue) {
                            if (is_numeric($acceptedValue) && $currentValue > $acceptedValue) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
                break;
        }

        return false;
    }
}
