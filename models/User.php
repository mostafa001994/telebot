<?php

class User
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function findByTelegramId($telegramId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE telegram_id=? LIMIT 1"
        );

        $stmt->execute([$telegramId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($user)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users
            (
                telegram_id,
                first_name,
                last_name,
                username,
                language_code,
                last_seen
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW()
            )
        ");

        return $stmt->execute([
            $user['telegram_id'],
            $user['first_name'],
            $user['last_name'],
            $user['username'],
            $user['language_code']
        ]);
    }

    public function update($user)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                first_name=?,
                last_name=?,
                username=?,
                language_code=?,
                last_seen=NOW()
            WHERE telegram_id=?
        ");

        return $stmt->execute([
            $user['first_name'],
            $user['last_name'],
            $user['username'],
            $user['language_code'],
            $user['telegram_id']
        ]);
    }

    public function savePhone($telegramId, $phone)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET phone=?
            WHERE telegram_id=?
        ");

        return $stmt->execute([
            $phone,
            $telegramId
        ]);
    }


    
    public function block($telegramId)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status=0
            WHERE telegram_id=?
        ");

        return $stmt->execute([$telegramId]);
    }

    public function unblock($telegramId)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status=1
            WHERE telegram_id=?
        ");

        return $stmt->execute([$telegramId]);
    }

    public function all()
    {
        return $this->db
            ->query("SELECT * FROM users")
            ->fetchAll(PDO::FETCH_ASSOC);
    }




    public function count()
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM users"
        );

        return $stmt->fetchColumn();
    }


    public function findById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE id=? LIMIT 1"
        );

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function makeAdmin($telegramId)
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET is_admin=1
        WHERE telegram_id=?
    ");

        return $stmt->execute([$telegramId]);
    }


    public function removeAdmin($telegramId)
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET is_admin=0
        WHERE telegram_id=?
    ");

        return $stmt->execute([$telegramId]);
    }



}