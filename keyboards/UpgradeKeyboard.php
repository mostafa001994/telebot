<?php

class UpgradeKeyboard
{
    public static function get()
    {
        return [

            "inline_keyboard" => [

                [
                    [
                        "text" => "⬆️ ارتقا به 3 ماهه",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "upgrade",
                            "plan" => 3
                        ])
                    ]
                ],

                [
                    [
                        "text" => "⬆️ ارتقا به 12 ماهه",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "upgrade",
                            "plan" => 12
                        ])
                    ]
                ]

            ]

        ];
    }
}