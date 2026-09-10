<?php

class SubscriptionKeyboard
{
    
    
    public static function categories(PDO $pdo): array
    {
        $stmt = $pdo->query("
            SELECT
                id,
                name
            FROM subscription_categories
            WHERE status = 'active'
            ORDER BY sort_order ASC, id ASC
        ");

        $categories = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        $keyboard = [];

        foreach ($categories as $category) {

            $keyboard[] = [
                [
                    "text" => $category['name'],
                    "callback_data" => json_encode([
                        "action" => "subscription",
                        "type" => "category",
                        "category_id" => (int) $category['id']
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ];
        }

        $keyboard[] = [
            [
                "text" => "🔙 بازگشت",
                "callback_data" => json_encode([
                    "action" => "subscription",
                    "type" => "back"
                ], JSON_UNESCAPED_UNICODE)
            ]
        ];

        return [
            "inline_keyboard" => $keyboard
        ];
    }


    
    public static function plans(
        PDO $pdo,
        int $categoryId
    ): array {
        $stmt = $pdo->prepare("
            SELECT
                id,
                name,
                duration_days,
                price,
                discount_price
            FROM subscription_plans
            WHERE category_id = ?
              AND status = 'active'
            ORDER BY sort_order ASC, id ASC
        ");

        $stmt->execute([
            $categoryId
        ]);

        $plans = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        $keyboard = [];

        foreach ($plans as $plan) {

            $price = self::getFinalPrice(
                $plan
            );

            $text =
                $plan['name']
                . " - "
                . number_format($price)
                . " تومان";

            $keyboard[] = [
                [
                    "text" => $text,
                    "callback_data" => json_encode([
                        "action" => "subscription",
                        "type" => "plan",
                        "plan_id" => (int) $plan['id']
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ];
        }

        $keyboard[] = [
            [
                "text" => "🔙 بازگشت به دسته‌بندی‌ها",
                "callback_data" => json_encode([
                    "action" => "subscription",
                    "type" => "categories"
                ], JSON_UNESCAPED_UNICODE)
            ]
        ];

        return [
            "inline_keyboard" => $keyboard
        ];
    }


    
    public static function plan(
        PDO $pdo,
        int $planId
    ): ?array {
        $stmt = $pdo->prepare("
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

        $plan = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$plan) {
            return null;
        }

        $price = self::getFinalPrice(
            $plan
        );

        $keyboard = [
            [
                [
                    "text" => "💳 خرید اشتراک",
                    "callback_data" => json_encode([
                        "action" => "subscription",
                        "type" => "buy",
                        "plan_id" => (int) $plan['id']
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ],
            [
                [
                    "text" => "🔙 بازگشت به پلن‌ها",
                    "callback_data" => json_encode([
                        "action" => "subscription",
                        "type" => "category",
                        "category_id" => (int) $plan['category_id']
                    ], JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];

        return [
            'plan' => $plan,
            'price' => $price,
            'keyboard' => [
                'inline_keyboard' => $keyboard
            ]
        ];
    }


    
    private static function getFinalPrice(
        array $plan
    ): int {
        if (
            isset($plan['discount_price']) &&
            $plan['discount_price'] !== null &&
            (int) $plan['discount_price'] > 0
        ) {
            return (int) $plan['discount_price'];
        }

        return (int) $plan['price'];
    }

    
}