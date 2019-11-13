<?php

namespace ClickerVolt;

require_once __DIR__ . '/redirect/router.php';

$router = new Router(Router::TYPE_QUERY_PARAMS);
$router->goToFinalURL();
