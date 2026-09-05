<?php

class HelpHandler extends BaseHandler
{
    public function handle()
    {
        Telegram::sendMessage(
            $this->chatId,
            "ℹ️ راهنما

📌 این ربات برای خرید اشتراک طراحی شده است.

🛒 مراحل خرید:
1. ورود به بخش خرید اشتراک
2. انتخاب پلن
3. پرداخت از طریق زرین‌پال
4. فعال شدن خودکار اشتراک

📱 در صورت نیاز به پشتیبانی از بخش پشتیبانی استفاده کنید.",
            MainKeyboard::get()
        );
    }
}