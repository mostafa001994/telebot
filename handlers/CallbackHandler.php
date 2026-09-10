<?php

class CallbackHandler
{
    private PDO $pdo;
    private array $callback;

    public function __construct(
        PDO $pdo,
        array $update
    ) {
        $this->pdo = $pdo;

        $this->callback =
            $update['callback_query'];
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

        if (
            ($data['action'] ?? '')
            !== 'subscription'
        ) {
            return;
        }

        $this->handleSubscription(
            $data
        );
    }

    private function handleSubscription(
        array $data
    ) {
        $type =
            $data['type'] ?? '';

        $telegramId =
            (int) $this->callback['from']['id'];

        $chatId =
            $this->callback['message']['chat']['id'];

        $userService =
            new UserService($this->pdo);

        $user =
            $userService->find(
                $telegramId
            );

        if (!$user) {

            Telegram::answerCallbackQuery(
                $this->callback['id'],
                "❌ کاربر پیدا نشد. لطفاً /start را بزنید.",
                true
            );

            return;
        }



        if ($type === 'categories') {

            Telegram::answerCallbackQuery(
                $this->callback['id']
            );

            Telegram::sendMessage(
                $chatId,
                "🛒 لطفاً دسته‌بندی اشتراک را انتخاب کنید:",
                SubscriptionKeyboard::categories(
                    $this->pdo
                )
            );

            return;
        }



        if ($type === 'category') {

            $categoryId =
                (int) (
                    $data['category_id'] ?? 0
                );

            if ($categoryId <= 0) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ دسته‌بندی نامعتبر است.",
                    true
                );

                return;
            }

            $stmt = $this->pdo->prepare("
                SELECT
                    id,
                    name
                FROM subscription_categories
                WHERE id = ?
                  AND status = 'active'
                LIMIT 1
            ");

            $stmt->execute([
                $categoryId
            ]);

            $category =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            if (!$category) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ دسته‌بندی پیدا نشد.",
                    true
                );

                return;
            }

            Telegram::answerCallbackQuery(
                $this->callback['id']
            );

            Telegram::sendMessage(
                $chatId,
                "📦 دسته‌بندی: {$category['name']}\n\n"
                . "لطفاً پلن موردنظر را انتخاب کنید:",
                SubscriptionKeyboard::plans(
                    $this->pdo,
                    $categoryId
                )
            );

            return;
        }


        

        if ($type === 'plan') {

            $planId =
                (int) (
                    $data['plan_id'] ?? 0
                );

            if ($planId <= 0) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ پلن نامعتبر است.",
                    true
                );

                return;
            }

            $result =
                SubscriptionKeyboard::plan(
                    $this->pdo,
                    $planId
                );

            if (!$result) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ پلن موردنظر پیدا نشد.",
                    true
                );

                return;
            }

            $plan =
                $result['plan'];

            $price =
                $result['price'];

            Telegram::answerCallbackQuery(
                $this->callback['id']
            );

            $text =
                "📦 {$plan['name']}\n\n"

                . "📁 دسته‌بندی: "
                . $plan['category_name']
                . "\n"

                . "⏱ مدت: "
                . $plan['duration_days']
                . " روز\n"

                . "💰 قیمت: "
                . number_format($price)
                . " تومان";

            if (!empty($plan['description'])) {

                $text .=
                    "\n\n📝 توضیحات:\n"
                    . $plan['description'];
            }

            Telegram::sendMessage(
                $chatId,
                $text,
                $result['keyboard']
            );

            return;
        }



        if ($type === 'buy') {

            $planId =
                (int) (
                    $data['plan_id'] ?? 0
                );

            if ($planId <= 0) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ پلن نامعتبر است.",
                    true
                );

                return;
            }

            $stmt = $this->pdo->prepare("
                SELECT
                    sp.*,
                    sc.name AS category_name

                FROM subscription_plans sp

                INNER JOIN subscription_categories sc
                    ON sc.id = sp.category_id

                WHERE sp.id = ?
                  AND sp.status = 'active'
                  AND sc.status = 'active'

                LIMIT 1
            ");

            $stmt->execute([
                $planId
            ]);

            $plan =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            if (!$plan) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ این پلن دیگر قابل خرید نیست.",
                    true
                );

                return;
            }

            $amount =
                !empty($plan['discount_price'])
                    ? (int) $plan['discount_price']
                    : (int) $plan['price'];

            if ($amount <= 0) {

                Telegram::answerCallbackQuery(
                    $this->callback['id'],
                    "❌ قیمت پلن معتبر نیست.",
                    true
                );

                return;
            }

            Telegram::answerCallbackQuery(
                $this->callback['id'],
                "در حال ایجاد درخواست پرداخت..."
            );

            $paymentService =
                new PaymentService(
                    $this->pdo
                );

            $paymentId =
                $paymentService->create(
                    (int) $user['id'],
                    $planId,
                    $amount
                );

            $zarinpal =
                new ZarinPalService();

            $callbackUrl =
                'https://telebot-sqzn.onrender.com/verify.php'
                . '?payment_id='
                . $paymentId;

            $result =
                $zarinpal->request(
                    $amount,
                    $callbackUrl
                );

            if (
                !isset($result['data']) ||
                !isset($result['data']['code']) ||
                (int) $result['data']['code'] !== 100 ||
                empty($result['data']['authority'])
            ) {

                file_put_contents(
                    __DIR__ . '/../zarinpal_error.log',
                    date('Y-m-d H:i:s')
                    . "\n"
                    . print_r(
                        $result,
                        true
                    )
                    . "\n----------------\n",
                    FILE_APPEND
                );

                Telegram::sendMessage(
                    $chatId,
                    "❌ ایجاد درخواست پرداخت با خطا مواجه شد.\n\n"
                    . "لطفاً چند لحظه بعد دوباره تلاش کنید."
                );

                return;
            }

            $authority =
                $result['data']['authority'];

            $paymentService->setAuthority(
                $paymentId,
                $authority
            );

            $url =
                "https://www.zarinpal.com/pg/StartPay/"
                . $authority;

            Telegram::sendMessage(
                $chatId,

                "💳 درخواست پرداخت ایجاد شد.\n\n"

                . "📦 پلن: "
                . $plan['name']
                . "\n"

                . "⏱ مدت: "
                . $plan['duration_days']
                . " روز\n"

                . "💰 مبلغ: "
                . number_format($amount)
                . " تومان\n\n"

                . "برای پرداخت روی لینک زیر بزنید:\n\n"
                . $url
            );

            return;
        }


        
    

        if ($type === 'back') {

            Telegram::answerCallbackQuery(
                $this->callback['id']
            );

            Telegram::sendMessage(
                $chatId,
                "🏠 منوی اصلی",
                MainKeyboard::get(
                    !empty($user['is_admin']),
                    $telegramId
                )
            );

            return;
        }


        

        Telegram::answerCallbackQuery(
            $this->callback['id'],
            "❌ عملیات نامعتبر است.",
            true
        );
    }
}