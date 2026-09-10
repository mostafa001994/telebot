<?php

class BuyHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService(
            $this->pdo
        );

        $user = $userService->find(
            $this->telegramId
        );

        if (!$user) {
            return;
        }

        Telegram::sendMessage(
            $this->chatId,
            "🛒 لطفاً دسته‌بندی اشتراک را انتخاب کنید:",
            SubscriptionKeyboard::categories(
                $this->pdo
            )
        );
    }
}