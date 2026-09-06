<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/services/PaymentService.php';
require_once __DIR__ . '/services/SubscriptionService.php';
require_once __DIR__ . '/services/ZarinPalService.php';

$paymentService = new PaymentService($pdo);
$subscriptionService = new SubscriptionService($pdo);
$zarinpal = new ZarinPalService();

$authority = $_GET['Authority'] ?? null;
$status = $_GET['Status'] ?? null;

if (!$authority) {
    exit('Invalid request');
}

/*
|--------------------------------------------------------------------------
| Find payment
|--------------------------------------------------------------------------
*/

$payment = $paymentService->findByAuthority($authority);

if (!$payment) {
    exit('Payment not found');
}

/*
|--------------------------------------------------------------------------
| Already paid
|--------------------------------------------------------------------------
*/

if ($paymentService->isPaid($authority)) {
    exit('Payment already processed');
}

/*
|--------------------------------------------------------------------------
| Check ZarinPal status
|--------------------------------------------------------------------------
*/

if (strtoupper((string) $status) !== 'OK') {
    exit('Payment was cancelled');
}

/*
|--------------------------------------------------------------------------
| Amount
|--------------------------------------------------------------------------
|
| Amount must be exactly the same unit/value used when requesting payment.
|
*/

$amount = (int) $payment['amount'];

/*
|--------------------------------------------------------------------------
| Verify payment
|--------------------------------------------------------------------------
*/

$result = $zarinpal->verify(
    $amount,
    $authority
);

/*
|--------------------------------------------------------------------------
| Check verify result
|--------------------------------------------------------------------------
*/

if (
    !isset($result['data']['code']) ||
    (int) $result['data']['code'] !== 100
) {

    file_put_contents(
        __DIR__ . '/zarinpal_verify_error.log',
        date('Y-m-d H:i:s') . "\n" .
        "Authority: " . $authority . "\n" .
        "Result:\n" .
        print_r($result, true) .
        "\n----------------\n",
        FILE_APPEND
    );

    exit('Payment verification failed');
}

/*
|--------------------------------------------------------------------------
| Get reference ID
|--------------------------------------------------------------------------
*/

$refId = (string) (
    $result['data']['ref_id']
    ?? ''
);

if ($refId === '') {
    exit('Invalid payment response');
}

/*
|--------------------------------------------------------------------------
| Mark payment as paid
|--------------------------------------------------------------------------
*/

$marked = $paymentService->markPaid(
    $authority,
    $refId
);

if (!$marked) {
    exit('Could not update payment');
}

/*
|--------------------------------------------------------------------------
| Create subscription
|--------------------------------------------------------------------------
*/

try {

    $subscriptionService->createNew(
        (int) $payment['user_id'],
        (int) $payment['id'],
        (int) $payment['plan']
    );

} catch (Throwable $e) {

    file_put_contents(
        __DIR__ . '/subscription_error.log',
        date('Y-m-d H:i:s') . "\n" .
        $e->__toString() .
        "\n----------------\n",
        FILE_APPEND
    );

    exit('Payment successful but subscription activation failed');
}

/*
|--------------------------------------------------------------------------
| Success
|--------------------------------------------------------------------------
*/

echo <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پرداخت موفق</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            max-width: 420px;
            width: calc(100% - 40px);
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        h1 {
            color: #16a34a;
        }

        p {
            color: #555;
            line-height: 2;
        }
    </style>
</head>
<body>

<div class="box">
    <div class="icon">✅</div>

    <h1>پرداخت موفق</h1>

    <p>
        پرداخت شما با موفقیت انجام شد.
        <br>
        اشتراک شما فعال گردید.
    </p>

    <p>
        می‌توانید به ربات تلگرام بازگردید.
    </p>
</div>

</body>
</html>
HTML;