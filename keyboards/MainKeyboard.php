<?php

class MainKeyboard
{
    public static function get(
        bool $isAdmin = false,
        int|string|null $telegramId = null
    ) {
        $keyboard = [
            [
                ["text" => BTN_BUY]
            ],
            [
                ["text" => BTN_PROFILE],
                ["text" => BTN_SUPPORT]
            ],
            [
                ["text" => BTN_HELP]
            ]
        ];

        /* Admin Panel */

        if ($isAdmin && $telegramId !== null) {

            $keyboard[] = [
                [
                    "text" => "⚙️ مدیریت ربات",
                    "web_app" => [
                        "url" =>
                            "https://telebot-sqzn.onrender.com/admin/"
                            . "?telegram_id="
                            . rawurlencode(
                                (string) $telegramId
                            )
                    ]
                ]
            ];
        }

        return [
            "keyboard" => $keyboard,
            "resize_keyboard" => true,
            "is_persistent" => true
        ];
    }
}
