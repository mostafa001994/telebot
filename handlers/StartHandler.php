<?php

class StartHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService($this->pdo);
        $stateService = new StateService($this->pdo);

        $telegramUser = [
            'telegram_id' => $this->telegramId,
            'first_name' => $this->message['from']['first_name'] ?? '',
            'last_name' => $this->message['from']['last_name'] ?? '',
            'username' => $this->message['from']['username'] ?? '',
            'language_code' => $this->message['from']['language_code'] ?? ''
        ];

        $user = $userService->sync($telegramUser);

        $stateService->clear($user['id']);


file_put_contents(
    __DIR__ . '/../debug.log',
    "CHAT ID: ".$this->chatId."\n",
    FILE_APPEND
);
        Telegram::sendMessage(
            $this->chatId,
            "سلام {$telegramUser['first_name']} 👋

به ربات فروش اشتراک خوش آمدید.",
            //MainKeyboard::get()
        );
    }
}