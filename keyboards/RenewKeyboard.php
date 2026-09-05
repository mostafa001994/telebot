<?php

class RenewKeyboard
{
    public static function get()
    {
        return [

            "inline_keyboard" => [

                [
                    [
                        "text" => "🔄 تمدید 1 ماهه",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "renew",
                            "plan" => 1
                        ])
                    ]
                ],

                [
                    [
                        "text" => "🔄 تمدید 3 ماهه",
                        "callback_data" => json_encode([
                            "action" => "subscription",
                            "type" => "renew",
                            "plan" => 3
                        ])
                    ]
                ]

            ]

        ];
    }
}