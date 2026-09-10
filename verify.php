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


//Check ZarinPal status

if (strtoupper((string) $status) !== 'OK') {

    $payment = $paymentService->findByAuthority(
        $authority
    );

    if ($payment) {
        $paymentService->markFailed(
            $authority
        );
    }

    exit('Payment was cancelled');
}


//Transaction

try {

    $pdo->beginTransaction();


    //Find and lock payment

    $payment =
        $paymentService->findByAuthorityForUpdate(
            $authority
        );

    if (!$payment) {
        $pdo->rollBack();
        exit('Payment not found');
    }


    //Already processed

    if ($payment['status'] === 'paid') {

        $pdo->commit();

        exit('Payment already processed');
    }


    //Validate payment plan

    $planId = (int) ($payment['plan_id'] ?? 0);

    if ($planId <= 0) {

        $pdo->rollBack();

        file_put_contents(
            __DIR__ . '/subscription_error.log',
            date('Y-m-d H:i:s') . "\n" .
            "Invalid plan_id\n" .
            "Payment ID: " . $payment['id'] . "\n" .
            "Authority: " . $authority . "\n" .
            "----------------\n",
            FILE_APPEND
        );

        exit('Invalid subscription plan');
    }


    //Validate amount

    $amount = (int) $payment['amount'];

    if ($amount <= 0) {

        $pdo->rollBack();

        exit('Invalid payment amount');
    }


    //Verify with ZarinPal

    $result = $zarinpal->verify(
        $amount,
        $authority
    );


    //Check verify result

    if (
        !isset($result['data']['code']) ||
        (int) $result['data']['code'] !== 100
    ) {

        $pdo->rollBack();

        file_put_contents(
            __DIR__ . '/zarinpal_verify_error.log',
            date('Y-m-d H:i:s') . "\n" .
            "Authority: " . $authority . "\n" .
            "Payment ID: " . $payment['id'] . "\n" .
            "Result:\n" .
            print_r($result, true) .
            "\n----------------\n",
            FILE_APPEND
        );

        exit('Payment verification failed');
    }


    //Reference ID

    $refId = (string) (
        $result['data']['ref_id']
        ?? ''
    );

    if ($refId === '') {

        $pdo->rollBack();

        exit('Invalid payment response');
    }


    //Mark payment as paid

    $marked = $paymentService->markPaid(
        $authority,
        $refId
    );

    if (!$marked) {

        $pdo->rollBack();

        exit('Could not update payment');
    }


    // Create subscription

    $subscriptionId =
        $subscriptionService->createNew(
            (int) $payment['user_id'],
            (int) $payment['id'],
            $planId
        );

    if (!$subscriptionId) {

        throw new Exception(
            'Subscription could not be created.'
        );
    }

    //Commit

    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    file_put_contents(
        __DIR__ . '/subscription_error.log',
        date('Y-m-d H:i:s') . "\n" .
        $e->__toString() .
        "\n----------------\n",
        FILE_APPEND
    );

    exit(
        'Payment successful but subscription activation failed'
    );
}


//Success

$planName =
    $payment['plan_name']
    ?? 'اشتراک';

$duration =
    (int) (
        $payment['duration_days']
        ?? 0
    );

$amount =
    number_format(
        (int) $payment['amount']
    );

echo <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>پرداخت موفق</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            background: #f5f5f5;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;

            margin: 0;
            padding: 20px;
        }

        .box {
            background: white;

            padding: 30px;

            border-radius: 16px;

            text-align: center;

            box-shadow:
                0 10px 30px rgba(0,0,0,.08);

            max-width: 420px;
            width: 100%;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        h1 {
            color: #16a34a;
            margin-bottom: 20px;
        }

        p {
            color: #555;
            line-height: 2;
            margin: 8px 0;
        }

        .info {
            background: #f8fafc;

            border-radius: 12px;

            padding: 15px;

            margin: 20px 0;

            text-align: right;
        }

        .info div {
            padding: 6px 0;
        }

        .success {
            color: #16a34a;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="box">

    <div class="icon">✅</div>

    <h1>پرداخت موفق</h1>

    <p>
        پرداخت شما با موفقیت انجام شد.
    </p>

    <div class="info">

        <div>
            📦 پلن:
            <strong>
                {$planName}
            </strong>
        </div>

        <div>
            💰 مبلغ:
            <strong>
                {$amount} تومان
            </strong>
        </div>

        <div>
            ⏱ مدت:
            <strong>
                {$duration} روز
            </strong>
        </div>

        <div>
            وضعیت:
            <span class="success">
                فعال
            </span>
        </div>

    </div>

    <p>
        اشتراک شما با موفقیت فعال شد.
    </p>

    <p>
        می‌توانید به ربات تلگرام بازگردید.
    </p>

</div>

</body>

</html>
HTML;