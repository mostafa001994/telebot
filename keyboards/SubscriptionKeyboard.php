<?php

class SubscriptionKeyboard
{
    public static function get()
    {
        return [

            "inline_keyboard" => [

                [
                    [
                        "text" => "🟢 1 ماهه - خرید",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "buy",
                            "plan" => 1
                        ])
                    ]
                ],

                [
                    [
                        "text" => "🔵 3 ماهه - خرید",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "buy",
                            "plan" => 3
                        ])
                    ]
                ],

                [
                    [
                        "text" => "🟣 12 ماهه - خرید",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "buy",
                            "plan" => 12
                        ])
                    ]
                ]

            ]

        ];
    }
}