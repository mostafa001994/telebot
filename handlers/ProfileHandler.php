<?php

class ProfileHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService($this->pdo);
        $subscriptionService = new SubscriptionService($this->pdo);

        $user = $userService->find($this->telegramId);

        if (!$user) {
            return;
        }

        $subscription = $subscriptionService->getActive($this->telegramId);

        $text = "👤 حساب کاربری شما

";

        $text .= "🆔 ID: {$user['id']}\n";
        $text .= "👤 نام: {$user['first_name']}\n";

        if (!empty($user['username'])) {
            $text .= "📛 یوزرنیم: @{$user['username']}\n";
        }

        if (!empty($user['phone'])) {
            $text .= "📱 شماره: {$user['phone']}\n";
        } else {
            $text .= "📱 شماره: ثبت نشده\n";
        }

        $text .= "\n──────────────────\n";

        if ($subscription) {

            $text .= "✅ وضعیت اشتراک: فعال\n";
            $text .= "📦 پلن: {$subscription['plan']}\n";
            $text .= "📅 شروع: {$subscription['started_at']}\n";
            $text .= "⏳ پایان: {$subscription['expired_at']}\n";

            $remaining = strtotime($subscription['expired_at']) - time();
            $daysLeft = ceil($remaining / 86400);

            $text .= "⏰ روزهای باقی‌مانده: {$daysLeft}\n";

        } else {

            $text .= "❌ وضعیت اشتراک: غیرفعال\n";
            $text .= "💡 شما در حال حاضر اشتراک فعال ندارید\n";
        }

        Telegram::sendMessage(
            $this->chatId,
            "👤 پنل کاربری",
            [
                "inline_keyboard" => [

                    [
                        [
                            "text" => "🛒 خرید",
                            "callback_data" => json_encode([
                                "action" => "subscription",
                                "type" => "buy",
                                "plan" => 1
                            ])
                        ]
                    ],

                    [
                        [
                            "text" => "🔄 تمدید",
                            "callback_data" => json_encode([
                                "action" => "subscription",
                                "type" => "renew",
                                "plan" => 1
                            ])
                        ]
                    ],

                    [
                        [
                            "text" => "⬆️ ارتقا",
                            "callback_data" => json_encode([
                                "action" => "subscription",
                                "type" => "upgrade",
                                "plan" => 3
                            ])
                        ]
                    ]

                ]
            ]
        );
    }
}