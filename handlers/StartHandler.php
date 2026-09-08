<?php

class StartHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService($this->pdo);
        $stateService = new StateService($this->pdo);

        /* Telegram User */

        $telegramUser = [
            'telegram_id' => $this->telegramId,
            'first_name' => $this->message['from']['first_name'] ?? '',
            'last_name' => $this->message['from']['last_name'] ?? '',
            'username' => $this->message['from']['username'] ?? '',
            'language_code' => $this->message['from']['language_code'] ?? ''
        ];

        /* Sync User */

        $user = $userService->sync($telegramUser);

        /* Clear Previous State */

        $stateService->clear(
            (int) $user['id']
        );

        /* Debug */

        file_put_contents(
            __DIR__ . '/../debug.log',
            "CHAT ID: " . $this->chatId . PHP_EOL,
            FILE_APPEND
        );

        /* Admin */

        $isAdmin = !empty($user['is_admin']);

        /* Main Keyboard */

        $keyboard = MainKeyboard::get(
            $isAdmin,
            $this->telegramId
        );

        /* Welcome Message */

        Telegram::sendMessage(
            $this->chatId,
            "سلام {$telegramUser['first_name']} 👋

به ربات فروش اشتراک خوش آمدید.",
            $keyboard
        );
    }
}
