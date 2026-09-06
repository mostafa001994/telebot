<?php

define('BOT_TOKEN', getenv('BOT_TOKEN') ?: '');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'bot');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASS', getenv('DB_PASS') ?: '');

define(
    'API_URL',
    'https://api.telegram.org/bot' . BOT_TOKEN . '/'
);

define('BOT_SECRET', getenv('BOT_SECRET') ?: '');;