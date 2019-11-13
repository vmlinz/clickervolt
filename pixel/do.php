<?php

namespace ClickerVolt;

require_once __DIR__ . '/actionHandler.php';
require_once __DIR__ . '/../redirect/session/sessionClick.php';

function processPixel()
{
    $info = [
        ActionHandler::URL_PARAM_SLUG => empty($_GET[ActionHandler::URL_PARAM_SLUG]) ? '' : $_GET[ActionHandler::URL_PARAM_SLUG],

        ActionHandler::URL_PARAM_ACTION_TYPE => empty($_GET[ActionHandler::URL_PARAM_ACTION_TYPE]) ? '' : $_GET[ActionHandler::URL_PARAM_ACTION_TYPE],
        ActionHandler::URL_PARAM_ACTION_NAME => empty($_GET[ActionHandler::URL_PARAM_ACTION_NAME]) ? '' : $_GET[ActionHandler::URL_PARAM_ACTION_NAME],
        ActionHandler::URL_PARAM_ACTION_REVENUE => empty($_GET[ActionHandler::URL_PARAM_ACTION_REVENUE]) ? 0.0 : $_GET[ActionHandler::URL_PARAM_ACTION_REVENUE],
    ];

    if (!empty($_GET[ActionHandler::URL_PARAM_CLICK_ID])) {

        $info[ActionHandler::URL_PARAM_CLICK_ID] = $_GET[ActionHandler::URL_PARAM_CLICK_ID];
    } else {

        // Try to get clickId from session

        $session = new SessionClick();
        $clickInfo = $session->getClickInfo();
        if ($clickInfo) {
            $info[ActionHandler::URL_PARAM_CLICK_ID] = $clickInfo->getClickId();
        }
    }

    if (!empty($info[ActionHandler::URL_PARAM_CLICK_ID]) && is_numeric($info[ActionHandler::URL_PARAM_ACTION_REVENUE])) {

        $actionHandler = new ActionHandler();
        $actionHandler->addAction([
            'clickId' => $info[ActionHandler::URL_PARAM_CLICK_ID],
            'actionType' => $info[ActionHandler::URL_PARAM_ACTION_TYPE],
            'actionName' => $info[ActionHandler::URL_PARAM_ACTION_NAME],
            'actionRevenue' => $info[ActionHandler::URL_PARAM_ACTION_REVENUE],
            'clickTimestamp' => null,
            'actionTimestamp' => time(),
            'restrictToSlug' => $info[ActionHandler::URL_PARAM_SLUG]
        ]);
    }
}

processPixel();

header('Content-Type: image/png');
die("\x89\x50\x4e\x47\x0d\x0a\x1a\x0a\x00\x00\x00\x0d\x49\x48\x44\x52\x00\x00\x00\x01\x00\x00\x00\x01\x01\x03\x00\x00\x00\x25\xdb\x56\xca\x00\x00\x00\x03\x50\x4c\x54\x45\x00\x00\x00\xa7\x7a\x3d\xda\x00\x00\x00\x01\x74\x52\x4e\x53\x00\x40\xe6\xd8\x66\x00\x00\x00\x0a\x49\x44\x41\x54\x08\xd7\x63\x60\x00\x00\x00\x02\x00\x01\xe2\x21\xbc\x33\x00\x00\x00\x00\x49\x45\x4e\x44\xae\x42\x60\x82");
