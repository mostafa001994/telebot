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
        $data = json_decode($this->callback['data'], true);

        if (!$data) {
            return;
        }

        if ($data['action'] === 'subscription') {

            $this->handleSubscription($data);
        }
    }

    private function handleSubscription($data)
    {
        $subscriptionService = new SubscriptionService($this->pdo);
        $paymentService = new PaymentService($this->pdo);

        $telegramId = $this->callback['from']['id'];
        $chatId = $this->callback['message']['chat']['id'];

        $plan = (int) $data['plan'];
        $type = $data['type'];

        Telegram::answerCallbackQuery(
            $this->callback['id'],
            "در حال پردازش..."
        );

        // BUY → پرداخت
        if ($type === 'buy') {

            $amount = match ($plan) {
                1 => 100000,
                3 => 250000,
                12 => 800000,
                default => 100000
            };

            $paymentId = $paymentService->create(
                $telegramId,
                $plan,
                $amount
            );

            $zarinpal = new ZarinPalService();

            $result = $zarinpal->request(
                $amount,
                "https://your-domain.com/verify.php?payment_id=$paymentId"
            );

            $authority = $result['data']['authority'];

            $paymentService->setAuthority($paymentId, $authority);

            $url = "https://www.zarinpal.com/pg/StartPay/" . $authority;

            Telegram::sendMessage(
                $chatId,
                "💳 پرداخت:\n\n$url"
            );

            return;
        }

        // RENEW
        if ($type === 'renew') {

            $subscriptionService->renew($telegramId, $plan);

            Telegram::sendMessage(
                $chatId,
                "🔄 اشتراک شما تمدید شد."
            );

            return;
        }

        // UPGRADE
        if ($type === 'upgrade') {

            $subscriptionService->upgrade($telegramId, $plan);

            Telegram::sendMessage(
                $chatId,
                "⬆️ اشتراک شما ارتقا یافت."
            );

            return;
        }
    }




}