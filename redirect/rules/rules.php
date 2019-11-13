<?php

namespace ClickerVolt;

class Rules
{

    const RULE_TYPE_COUNTRY = 'country';
    const RULE_TYPE_REGION = 'region';
    const RULE_TYPE_CITY = 'city';
    const RULE_TYPE_ISP = 'isp';
    const RULE_TYPE_IP = 'ip';
    const RULE_TYPE_LANGUAGE = 'language';
    const RULE_TYPE_USER_AGENT = 'ua';
    const RULE_TYPE_DEVICE_TYPE = 'device-type';
    const RULE_TYPE_DEVICE_BRAND = 'device-brand';
    const RULE_TYPE_DEVICE_NAME = 'device-name';
    const RULE_TYPE_OS = 'os';
    const RULE_TYPE_OS_VERSION = 'os-version';
    const RULE_TYPE_BROWSER = 'browser';
    const RULE_TYPE_BROWSER_VERSION = 'browser-version';
    const RULE_TYPE_DATE = 'date';
    const RULE_TYPE_DAY_OF_WEEK = 'day-of-week';
    const RULE_TYPE_HOUR = 'hour';
    const RULE_TYPE_URL = 'url';
    const RULE_TYPE_URL_VARIABLE = 'url-var';
    const RULE_TYPE_REFERRER = 'referrer';

    const OPERATOR_IS = 'is';
    const OPERATOR_IS_NOT = 'is-not';
    const OPERATOR_GREATER_THAN = 'gt';
    const OPERATOR_GREATER_THAN_OR_EQUAL = 'gte';
    const OPERATOR_LESS_THAN = 'lt';
    const OPERATOR_LESS_THAN_OR_EQUAL = 'lte';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_CONTAINS_NOT = 'contains-not';
    const OPERATOR_EMPTY = 'empty';
    const OPERATOR_EMPTY_NOT = 'empty-not';

    const OPERATORS = [
        self::OPERATOR_IS,
        self::OPERATOR_IS_NOT,
        self::OPERATOR_GREATER_THAN,
        self::OPERATOR_GREATER_THAN_OR_EQUAL,
        self::OPERATOR_LESS_THAN,
        self::OPERATOR_LESS_THAN_OR_EQUAL,
        self::OPERATOR_CONTAINS,
        self::OPERATOR_CONTAINS_NOT,
        self::OPERATOR_EMPTY,
        self::OPERATOR_EMPTY_NOT,
    ];

    const VALUE_SEPARATOR = ' &gt; ';
}
