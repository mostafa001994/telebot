<?php

class ProfileKeyboard
{
    public static function get()
    {
        return [

            "keyboard" => [

                [
                    [
                        "text" => "🛒 خرید اشتراک"
                    ]
                ],

                [
                    [
                        "text" => "📋 تمدید اشتراک"
                    ],
                    [
                        "text" => "⬆️ ارتقا اشتراک"
                    ]
                ],

                [
                    [
                        "text" => "📱 ثبت/تغییر شماره"
                    ]
                ],

                [
                    [
                        "text" => BTN_BACK
                    ]
                ]

            ],

            "resize_keyboard" => true

        ];
    }
}