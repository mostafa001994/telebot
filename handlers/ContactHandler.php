<?php

class ContactHandler extends BaseHandler
{
    public function handle()
    {
        $userService = new UserService($this->pdo);
        $stateService = new StateService($this->pdo);

        $contact = $this->message['contact'] ?? null;

        if (!$contact) {
            return;
        }

        $user = $userService->find($this->telegramId);

        if (!$user) {
            return;
        }

        $userService->savePhone(
            $this->telegramId,
            $contact['phone_number']
        );

        $stateService->clear(
            $user['id']
        );

        Telegram::sendMessage(
            $this->chatId,
            "✅ شماره تماس شما ثبت شد."
        );
    }
}