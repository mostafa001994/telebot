<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

function debugLog(string $text): void
{
    file_put_contents(
        __DIR__ . '/debug.log',
        '[' . date('Y-m-d H:i:s') . "] {$text}\n",
        FILE_APPEND
    );
}

set_exception_handler(function (Throwable $e) {

    error_log(
        "===== EXCEPTION =====\n" .
        $e->__toString()
    );

    http_response_code(500);

    echo "ERROR: " . $e->getMessage();

    exit;
});

register_shutdown_function(function () {

    $error = error_get_last();

    if ($error !== null) {

        error_log(
            "===== FATAL ERROR =====\n" .
            print_r($error, true)
        );
    }
});

debugLog("Start");

require_once __DIR__ . '/config/config.php';
debugLog("config loaded");

require_once __DIR__ . '/config/database.php';
debugLog("database loaded");

require_once __DIR__ . '/config/constants.php';
debugLog("constants loaded");

$secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

if ($secret !== BOT_SECRET) {
    debugLog("Forbidden");
    http_response_code(403);
    exit('Forbidden');
}

debugLog("Secret OK");


// Core
require_once __DIR__ . '/core/Telegram.php';
debugLog("Telegram loaded");

require_once __DIR__ . '/core/Router.php';
debugLog("Router loaded");


// Base Handler
require_once __DIR__ . '/handlers/BaseHandler.php';
debugLog("BaseHandler loaded");


// Models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/UserState.php';
debugLog("Models loaded");


// Services
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/StateService.php';
require_once __DIR__ . '/services/SubscriptionService.php';
require_once __DIR__ . '/services/PaymentService.php';
require_once __DIR__ . '/services/ZarinPalService.php';
debugLog("Services loaded");


// Keyboards
require_once __DIR__ . '/keyboards/MainKeyboard.php';
require_once __DIR__ . '/keyboards/SubscriptionKeyboard.php';
require_once __DIR__ . '/keyboards/BackKeyboard.php';
debugLog("Keyboards loaded");


// Handlers
require_once __DIR__ . '/handlers/StartHandler.php';
require_once __DIR__ . '/handlers/BuyHandler.php';
require_once __DIR__ . '/handlers/ProfileHandler.php';
require_once __DIR__ . '/handlers/SupportHandler.php';
require_once __DIR__ . '/handlers/HelpHandler.php';
require_once __DIR__ . '/handlers/CallbackHandler.php';
require_once __DIR__ . '/handlers/MessageHandler.php';
require_once __DIR__ . '/handlers/ContactHandler.php';
debugLog("Handlers loaded");


$update = json_decode(
    file_get_contents('php://input'),
    true
);

debugLog("Update received");

Router::dispatch($pdo, $update);

debugLog("Router finished");