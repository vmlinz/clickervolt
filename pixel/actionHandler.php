<?php

namespace ClickerVolt;

require_once __DIR__ . '/../db/tableActions.php';
require_once __DIR__ . '/../utils/cookieTools.php';

class ActionHandler
{

    const URL_PARAM_CLICK_ID = 'cid';
    const URL_PARAM_SLUG = 'slug';

    const URL_PARAM_ACTION_TYPE = 'type';
    const URL_PARAM_ACTION_NAME = 'name';
    const URL_PARAM_ACTION_REVENUE = 'amount';

    /**
     * @param string $clickId
     * @param string $actionType
     * @param string $actionName
     * @param double $revenue
     * @param string $restrictToSlug - if set, then ensures that the action is not stored if the specified clickId is not for that slug
     */
    function addAction($actionParams = [])
    {

        $defaultActionParams = [
            'clickId' => null,
            'actionType' => null,
            'actionName' => null,
            'actionRevenue' => null,
            'clickTimestamp' => null,
            'actionTimestamp' => null,
            'restrictToSlug' => null
        ];
        $actionParams = array_merge($defaultActionParams, $actionParams);

        if (!CookieTools::isLoggedIn()) {
            $action = new Action($actionParams);
            $action->queue();
        }
    }
}
