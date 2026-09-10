<?php

class ProfileHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService(
            $this->pdo
        );

        $subscriptionService = new SubscriptionService(
            $this->pdo
        );

        $paymentService = new PaymentService(
            $this->pdo
        );

        $user = $userService->find(
            $this->telegramId
        );

        if (!$user) {
            Telegram::sendMessage(
                $this->chatId,
                "❌ اطلاعات کاربر پیدا نشد.\n\nلطفاً /start را بزنید."
            );

            return;
        }

        $userId = (int) $user['id'];

        
        //Active Subscription
   
        $subscription =
            $subscriptionService->getActive(
                $userId
            );


        //User Payments

        $payments =
            $paymentService->getUserPayments(
                $userId
            );



        //Profile

        $text = "👤 حساب کاربری شما\n\n";

        $text .= "🆔 ID: {$user['id']}\n";

        $text .= "👤 نام: "
            . ($user['first_name'] ?: '-')
            . "\n";

        if (!empty($user['last_name'])) {
            $text .= "👤 نام خانوادگی: "
                . $user['last_name']
                . "\n";
        }

        if (!empty($user['username'])) {
            $text .= "📛 یوزرنیم: @"
                . $user['username']
                . "\n";
        }

        if (!empty($user['phone'])) {
            $text .= "📱 شماره: "
                . $user['phone']
                . "\n";
        } else {
            $text .= "📱 شماره: ثبت نشده\n";
        }

        $text .= "\n──────────────────\n\n";


        //Active Subscription

        $text .= "📋 اشتراک فعلی\n\n";

        if ($subscription) {

            $text .= "🟢 وضعیت: فعال\n";

            if (!empty($subscription['category_name'])) {
                $text .= "📁 دسته‌بندی: "
                    . $subscription['category_name']
                    . "\n";
            }

            if (!empty($subscription['plan_name'])) {
                $text .= "📦 پلن: "
                    . $subscription['plan_name']
                    . "\n";
            }

            if (!empty($subscription['duration_days'])) {
                $text .= "⏱ مدت: "
                    . $subscription['duration_days']
                    . " روز\n";
            }

            $text .= "📅 شروع: "
                . $subscription['started_at']
                . "\n";

            $text .= "⏳ پایان: "
                . $subscription['expired_at']
                . "\n";

            $remaining =
                strtotime($subscription['expired_at'])
                - time();

            $daysLeft = max(
                0,
                (int) ceil(
                    $remaining / 86400
                )
            );

            $text .= "⏰ روزهای باقی‌مانده: "
                . $daysLeft
                . "\n";

        } else {

            $text .= "🔴 وضعیت: غیرفعال\n";
            $text .= "💡 شما در حال حاضر اشتراک فعالی ندارید.\n";
        }


        // Payment Summary

        $text .= "\n──────────────────\n\n";

        $text .= "💳 پرداخت‌ها\n\n";

        if (empty($payments)) {

            $text .= "هنوز پرداختی ثبت نشده است.\n";

        } else {

            $paidCount = 0;
            $pendingCount = 0;
            $failedCount = 0;

            foreach ($payments as $payment) {

                if ($payment['status'] === 'paid') {
                    $paidCount++;
                }

                if ($payment['status'] === 'pending') {
                    $pendingCount++;
                }

                if ($payment['status'] === 'failed') {
                    $failedCount++;
                }
            }

            $text .= "✅ موفق: {$paidCount}\n";
            $text .= "⏳ در انتظار: {$pendingCount}\n";
            $text .= "❌ ناموفق: {$failedCount}\n";

            $text .= "\nآخرین پرداخت‌ها:\n";

            $shown = 0;

            foreach ($payments as $payment) {

                if ($shown >= 3) {
                    break;
                }

                $planName =
                    $payment['plan_name']
                    ?? 'پلن حذف‌شده';

                $amount =
                    number_format(
                        (int) $payment['amount']
                    );

                $status = match (
                    $payment['status']
                ) {
                    'paid' =>
                        "✅ موفق",

                    'pending' =>
                        "⏳ در انتظار",

                    'failed' =>
                        "❌ ناموفق",

                    default =>
                        "❔ نامشخص"
                };

                $text .= "\n";
                $text .= "💳 #{$payment['id']}\n";
                $text .= "📦 {$planName}\n";
                $text .= "💰 {$amount} تومان\n";
                $text .= "{$status}\n";

                $shown++;
            }
        }

        

        

        //Keyboard

        $keyboard = [
            [
                [
                    "text" => "🛒 خرید اشتراک",
                    "callback_data" => json_encode([
                        "action" => "subscription",
                        "type" => "categories"
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ],

            [
                [
                    "text" => "📋 اشتراک‌های من",
                    "callback_data" => json_encode([
                        "action" => "profile",
                        "type" => "subscriptions"
                    ], JSON_UNESCAPED_UNICODE)
                ],

                [
                    "text" => "💳 پرداخت‌های من",
                    "callback_data" => json_encode([
                        "action" => "profile",
                        "type" => "payments"
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ],

            [
                [
                    "text" => "🔙 بازگشت",
                    "callback_data" => json_encode([
                        "action" => "profile",
                        "type" => "back"
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];

        Telegram::sendMessage(
            $this->chatId,
            $text,
            [
                "inline_keyboard" => $keyboard
            ]
        );
    }
}