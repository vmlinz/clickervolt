<?php

namespace ClickerVolt;

interface Redirector
{

    const OPTION_SLUG = 'slug';
    const OPTION_REDIRECT_HOOKS = 'rhooks';

    function redirectTo($url, $options = []);
}
