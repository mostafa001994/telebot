<?php

class SupportHandler extends BaseHandler
{
    public function handle()
    {
        // اگر خواستی می‌تونی state بذاری برای سیستم تیکت در آینده
        $stateService = new StateService($this->pdo);

        $stateService->set(
            $this->telegramId,
            "support"
        );

        Telegram::sendMessage(
            $this->chatId,
            "☎️ پشتیبانی

برای ارتباط با پشتیبانی پیام خود را ارسال کنید.
در اولین فرصت پاسخ داده می‌شود.",
            BackKeyboard::get()
        );
    }
}