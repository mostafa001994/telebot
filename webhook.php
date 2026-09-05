<?php

$secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

if ($secret !== 'MY_SECRET_123') {
    http_response_code(403);
    exit('Forbidden');
}