<?php

class UserState
{

    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function get($userId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM user_states
            WHERE user_id=?
        ");

        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function set($userId, $state, $data = null)
    {
        $stmt = $this->db->prepare("
        INSERT INTO user_states
        (user_id,state,data)

        VALUES(?,?,?)

        ON DUPLICATE KEY UPDATE

        state=?,

        data=?
        ");

        return $stmt->execute([
            $userId,
            $state,
            json_encode($data),

            $state,
            json_encode($data)
        ]);
    }

    public function clear($userId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM user_states
            WHERE user_id=?
        ");

        return $stmt->execute([$userId]);
    }




    public function updateData(
        int $userId,
        array $data
    ) {
        $stmt = $this->db->prepare(
            "UPDATE user_states
         SET data=?
         WHERE user_id=?"
        );

        return $stmt->execute([
            json_encode($data),
            $userId
        ]);
    }

}