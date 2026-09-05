<?php

require 'config/config.php';
require 'config/database.php';

$paymentService = new PaymentService($pdo);
$subscriptionService = new SubscriptionService($pdo);
$zarinpal = new ZarinPalService();

$authority = $_GET['Authority'] ?? null;

if (!$authority) {
    exit('Invalid request');
}

/*  پیدا کردن پرداخت */
$payment = $paymentService->findByAuthority($authority);

if (!$payment) {
    exit('Payment not found');
}

/* جلوگیری از دوباره پردازش (خیلی مهم) */
if ($paymentService->isPaid($authority)) {
    exit('Already processed');
}

/*  گرفتن amount از دیتابیس (امن) */
$amount = (int) $payment['amount'];

/* verify از زرین‌پال */
$result = $zarinpal->verify($amount, $authority);

/* بررسی نتیجه */
if (!isset($result['data']['code']) || $result['data']['code'] != 100) {
    exit('Payment failed');
}

/* mark paid (atomic action) */
$refId = $result['data']['ref_id'];

$paymentService->markPaid($authority, $refId);

/* ساخت اشتراک (فقط یکبار) */
$subscriptionService->createNew(
    $payment['user_id'],
    $payment['id'],
    $payment['plan']
);

echo "Payment Successful";