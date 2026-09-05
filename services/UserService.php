<?php

class UserService
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function sync(array $telegramUser): array
    {
        $user = $this->userModel->findByTelegramId($telegramUser['telegram_id']);

        if (!$user) {

            $this->userModel->create($telegramUser);

        } else {

            $this->userModel->update($telegramUser);

        }

        return $this->userModel->findByTelegramId(
            $telegramUser['telegram_id']
        );
    }

    public function savePhone(
        int|string $telegramId,
        string $phone
    ): bool {
        return $this->userModel->savePhone(
            $telegramId,
            $phone
        );
    }

    public function find(
        int|string $telegramId
    ) {
        return $this->userModel
            ->findByTelegramId($telegramId);
    }

    public function block(
        int|string $telegramId
    ): bool {
        return $this->userModel->block(
            $telegramId
        );
    }

    public function unblock(
        int|string $telegramId
    ): bool {
        return $this->userModel->unblock(
            $telegramId
        );
    }

    public function all()
    {
        return $this->userModel->all();
    }

    public function count()
    {
        return $this->userModel->count();
    }






    


}