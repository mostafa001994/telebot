<?php

class MainKeyboard
{
    public static function get()
    {
        return [

            "keyboard" => [

                [
                    [
                        "text" => BTN_BUY
                    ]
                ],

                [
                    [
                        "text" => BTN_PROFILE
                    ],
                    [
                        "text" => BTN_SUPPORT
                    ]
                ],

                [
                    [
                        "text" => BTN_HELP
                    ]
                ]

            ],

            "resize_keyboard" => true,
            "is_persistent" => true

        ];
    }
}