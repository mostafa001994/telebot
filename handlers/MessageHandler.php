<?php

class MessageHandler extends BaseHandler
{
    public function handle()
    {
        Telegram::sendMessage(

            $this->chatId,

            "لطفاً از منوی ربات استفاده کنید.",

            MainKeyboard::get()

        );
    }
}