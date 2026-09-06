<?php

$token = getenv('BOT_TOKEN');
$secret = getenv('BOT_SECRET');

$url = 'https://telebot-sqzn.onrender.com/';

$ch = curl_init(
    'https://api.telegram.org/bot' . $token . '/setWebhook'
);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'url' => $url,
        'secret_token' => $secret,
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);

if ($response === false) {
    exit('CURL ERROR: ' . curl_error($ch));
}

curl_close($ch);

header('Content-Type: application/json');
echo $response;