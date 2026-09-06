<?php

class CallbackHandler
{
    private PDO $pdo;
    private array $callback;

    public function __construct(PDO $pdo, array $update)
    {
        $this->pdo = $pdo;
        $this->callback = $update['callback_query'];
    }

    public function handle()
    {
        $data = json_decode(
            $this->callback['data'] ?? '',
            true
        );

        if (!is_array($data)) {
            Telegram::answerCallbackQuery(
                $this->callback['id'],
                "❌ درخواست نامعتبر است.",
                true
            );

            return;
        }

        if (($data['action'] ?? '') === 'subscription') {
            $this->handleSubscription($data);
        }
    }

    private function handleSubscription(array $data)
    {
        $userService = new UserService($this->pdo);
        $paymentService = new PaymentService($this->pdo);
        $subscriptionService = new SubscriptionService($this->pdo);

        $telegramId = (int) $this->callback['from']['id'];
        $chatId = $this->callback['message']['chat']['id'];

        $plan = (int) ($data['plan'] ?? 0);
        $type = $data['type'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Validate plan
        |--------------------------------------------------------------------------
        */

        $plans = [
            1 => 100000,
            3 => 250000,
            12 => 800000,
        ];

        if (!isset($plans[$plan])) {

            Telegram::answerCallbackQuery(
                $this->callback['id'],
                "❌ پلن انتخاب‌شده معتبر نیست.",
                true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find user
        |--------------------------------------------------------------------------
        */

        $user = $userService->find($telegramId);

        if (!$user) {

            Telegram::answerCallbackQuery(
                $this->callback['id'],
                "❌ کاربر پیدا نشد. لطفاً /start را بزنید.",
                true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Answer callback
        |--------------------------------------------------------------------------
        */

        Telegram::answerCallbackQuery(
            $this->callback['id'],
            "در حال پردازش..."
        );

        /*
        |--------------------------------------------------------------------------
        | BUY
        |--------------------------------------------------------------------------
        */

        if ($type === 'buy') {

            $amount = $plans[$plan];

            /*
            |--------------------------------------------------------------------------
            | Create pending payment
            |--------------------------------------------------------------------------
            */

            $paymentId = $paymentService->create(
                (int) $user['id'],
                $plan,
                $amount
            );

            /*
            |--------------------------------------------------------------------------
            | ZarinPal
            |--------------------------------------------------------------------------
            */

            $zarinpal = new ZarinPalService();

            $callbackUrl =
                'https://telebot-sqzn.onrender.com/verify.php'
                . '?payment_id=' . $paymentId;

            $result = $zarinpal->request(
                $amount,
                $callbackUrl
            );

            /*
            |--------------------------------------------------------------------------
            | Check ZarinPal response
            |--------------------------------------------------------------------------
            */

            if (
                !isset($result['data']) ||
                !isset($result['data']['code']) ||
                (int) $result['data']['code'] !== 100 ||
                empty($result['data']['authority'])
            ) {

                file_put_contents(
                    __DIR__ . '/../zarinpal_error.log',
                    date('Y-m-d H:i:s') . "\n" .
                    print_r($result, true) .
                    "\n----------------\n",
                    FILE_APPEND
                );

                Telegram::sendMessage(
                    $chatId,
                    "❌ ایجاد درخواست پرداخت با خطا مواجه شد.\n\n"
                    . "لطفاً چند لحظه بعد دوباره تلاش کنید."
                );

                return;
            }

            $authority = $result['data']['authority'];

            /*
            |--------------------------------------------------------------------------
            | Save authority
            |--------------------------------------------------------------------------
            */

            $paymentService->setAuthority(
                $paymentId,
                $authority
            );

            /*
            |--------------------------------------------------------------------------
            | Payment URL
            |--------------------------------------------------------------------------
            */

            $url =
                "https://www.zarinpal.com/pg/StartPay/"
                . $authority;

            Telegram::sendMessage(
                $chatId,
                "💳 پرداخت اشتراک\n\n"
                . "📦 پلن: {$plan} ماهه\n"
                . "💰 مبلغ: " . number_format($amount) . " تومان\n\n"
                . "برای پرداخت روی لینک زیر بزنید:\n\n"
                . $url
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | RENEW
        |--------------------------------------------------------------------------
        */

        if ($type === 'renew') {

            $subscriptionService->renew(
                $telegramId,
                $plan
            );

            Telegram::sendMessage(
                $chatId,
                "🔄 اشتراک شما با موفقیت تمدید شد.",
                MainKeyboard::get()
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | UPGRADE
        |--------------------------------------------------------------------------
        */

        if ($type === 'upgrade') {

            $subscriptionService->upgrade(
                $telegramId,
                $plan
            );

            Telegram::sendMessage(
                $chatId,
                "⬆️ اشتراک شما با موفقیت ارتقا یافت.",
                MainKeyboard::get()
            );

            return;
        }

        Telegram::sendMessage(
            $chatId,
            "❌ عملیات نامعتبر است.",
            MainKeyboard::get()
        );
    }
}