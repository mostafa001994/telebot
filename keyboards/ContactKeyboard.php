<?php

class ContactKeyboard
{
    public static function get()
    {
        return [

            "keyboard" => [

                [
                    [

                        "text" => BTN_SEND_CONTACT,

                        "request_contact" => true

                    ]
                ],

                [
                    [
                        "text" => BTN_SKIP
                    ]
                ]

            ],

            "resize_keyboard" => true,
            "one_time_keyboard" => true

        ];
    }
}