<?php

class PaymentService
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function create(int $userId, int $plan, int $amount): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO payments
            (user_id, plan, amount, status)
            VALUES (?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $userId,
            $plan,
            $amount
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function setAuthority(int $paymentId, string $authority): bool
    {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET authority = ?
            WHERE id = ?
        ");

        return $stmt->execute([$authority, $paymentId]);
    }

    public function markPaid(string $authority, string $refId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET status = 'paid',
                ref_id = ?
            WHERE authority = ?
        ");

        return $stmt->execute([$refId, $authority]);
    }




    public function isPaid($authority): bool
    {
        $stmt = $this->db->prepare("
        SELECT status
        FROM payments
        WHERE authority = ?
        LIMIT 1
    ");

        $stmt->execute([$authority]);

        $status = $stmt->fetchColumn();

        return $status === 'paid';
    }


    public function findByAuthority(string $authority)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM payments
            WHERE authority = ?
            LIMIT 1
        ");

        $stmt->execute([$authority]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM payments
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }









}