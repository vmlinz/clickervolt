<?php

namespace ClickerVolt;

require_once __DIR__ . '/cron.php';

Cron::processClicksQueue(true);
