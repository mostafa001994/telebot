<?php

class SupportHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService($this->pdo);
        $stateService = new StateService($this->pdo);

        $user = $userService->find($this->telegramId);

        if (!$user) {
            return;
        }

        $stateService->set(
            (int) $user['id'],
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