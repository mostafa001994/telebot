<?php

class StateService
{
    private UserState $stateModel;

    public function __construct(PDO $pdo)
    {
        $this->stateModel = new UserState($pdo);
    }

    public function get(int $userId)
    {
        return $this->stateModel->get($userId);
    }

    public function set(
        int $userId,
        string $state,
        array|null $data = null
    )
    {
        return $this->stateModel->set(
            $userId,
            $state,
            $data
        );
    }

    public function clear(
        int $userId
    )
    {
        return $this->stateModel->clear(
            $userId
        );
    }

    public function updateData(
        int $userId,
        array $data
    )
    {
        return $this->stateModel->updateData(
            $userId,
            $data
        );
    }
}