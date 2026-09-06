<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');

error_reporting(E_ALL);

echo "STEP 1: index started<br>";

require_once __DIR__ . '/config/config.php';

echo "STEP 2: config loaded<br>";

require_once __DIR__ . '/config/database.php';

echo "STEP 3: database loaded<br>";

require_once __DIR__ . '/config/constants.php';

echo "STEP 4: constants loaded<br>";

$secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

echo "STEP 5: checking secret<br>";

if ($secret !== BOT_SECRET) {
    echo "STEP 6: secret invalid<br>";
    http_response_code(403);
    exit('Forbidden');
}

echo "STEP 6: secret OK<br>";

require_once __DIR__ . '/core/Telegram.php';

echo "STEP 7: Telegram loaded<br>";

require_once __DIR__ . '/handlers/BaseHandler.php';

echo "STEP 8: BaseHandler loaded<br>";

require_once __DIR__ . '/core/Router.php';

echo "STEP 9: Router loaded<br>";

require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/UserState.php';

echo "STEP 10: Models loaded<br>";

require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/StateService.php';

echo "STEP 11: Services loaded<br>";

require_once __DIR__ . '/keyboards/MainKeyboard.php';

require_once __DIR__ . '/handlers/StartHandler.php';
require_once __DIR__ . '/handlers/BuyHandler.php';
require_once __DIR__ . '/handlers/ProfileHandler.php';
require_once __DIR__ . '/handlers/SupportHandler.php';
require_once __DIR__ . '/handlers/HelpHandler.php';
require_once __DIR__ . '/handlers/CallbackHandler.php';
require_once __DIR__ . '/handlers/MessageHandler.php';
require_once __DIR__ . '/handlers/ContactHandler.php';

echo "STEP 12: Handlers loaded<br>";

$update = json_decode(
    file_get_contents('php://input'),
    true
);

echo "STEP 13: Update received<br>";

Router::dispatch($pdo, $update);

echo "STEP 14: Router finished<br>";