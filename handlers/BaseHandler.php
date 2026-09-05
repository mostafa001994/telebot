<?php

abstract class BaseHandler
{
    protected PDO $pdo;

    protected array $update;

    protected array $message;

    protected int $chatId;

    protected int $telegramId;

    public function __construct(PDO $pdo, array $update)
    {
        $this->pdo = $pdo;

        $this->update = $update;

        $this->message = $update['message'];

        $this->chatId = $this->message['chat']['id'];

        $this->telegramId = $this->message['from']['id'];
    }

    abstract public function handle();
}