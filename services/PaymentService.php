<?php

class PaymentService
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }



    //Create payment
    public function create(
        int $userId,
        int $planId,
        int $amount
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO payments
            (
                user_id,
                plan_id,
                plan,
                amount,
                status
            )
            VALUES (?, ?, 0, ?, 'pending')
        ");

        $stmt->execute([
            $userId,
            $planId,
            $amount
        ]);

        return (int) $this->db->lastInsertId();
    }


    //setAuthority

    public function setAuthority(
        int $paymentId,
        string $authority
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET authority = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $authority,
            $paymentId
        ]);
    }

    //Succeded payments 
    public function markPaid(
        string $authority,
        string $refId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET status = 'paid',
                ref_id = ?,
                updated_at = NOW()
            WHERE authority = ?
              AND status = 'pending'
        ");

        return $stmt->execute([
            $refId,
            $authority
        ]);
    }

    //Check payment
    public function isPaid(string $authority): bool
    {
        $stmt = $this->db->prepare("
            SELECT status
            FROM payments
            WHERE authority = ?
            LIMIT 1
        ");

        $stmt->execute([
            $authority
        ]);

        return $stmt->fetchColumn() === 'paid';
    }

    //find By Authority

    public function findByAuthority(string $authority)
    {
        $stmt = $this->db->prepare("
        SELECT
            p.*,

            sp.name AS plan_name,
            sp.duration_days,
            sp.price,
            sp.discount_price,

            sc.name AS category_name

        FROM payments p

        LEFT JOIN subscription_plans sp
            ON sp.id = p.plan_id

        LEFT JOIN subscription_categories sc
            ON sc.id = sp.category_id

        WHERE p.authority = ?

        LIMIT 1
    ");

        $stmt->execute([
            $authority
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function findByAuthorityForUpdate(string $authority)
    {
        $stmt = $this->db->prepare("
        SELECT
            p.*,

            sp.name AS plan_name,
            sp.duration_days,
            sp.price,
            sp.discount_price,

            sc.name AS category_name

        FROM payments p

        LEFT JOIN subscription_plans sp
            ON sp.id = p.plan_id

        LEFT JOIN subscription_categories sc
            ON sc.id = sp.category_id

        WHERE p.authority = ?

        LIMIT 1

        FOR UPDATE
    ");

        $stmt->execute([
            $authority
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    //
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                sp.name AS plan_name,
                sp.duration_days,
                sp.price,
                sp.discount_price,
                sc.name AS category_name
            FROM payments p

            LEFT JOIN subscription_plans sp
                ON sp.id = p.plan_id

            LEFT JOIN subscription_categories sc
                ON sc.id = sp.category_id

            WHERE p.id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function getUserPayments(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                sp.name AS plan_name,
                sp.duration_days,
                sc.name AS category_name

            FROM payments p

            LEFT JOIN subscription_plans sp
                ON sp.id = p.plan_id

            LEFT JOIN subscription_categories sc
                ON sc.id = sp.category_id

            WHERE p.user_id = ?

            ORDER BY p.id DESC
        ");

        $stmt->execute([
            $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function markFailed(string $authority): bool
    {
        $stmt = $this->db->prepare("
        UPDATE payments

        SET status = 'failed',
            updated_at = NOW()

        WHERE authority = ?
          AND status = 'pending'
    ");

        return $stmt->execute([
            $authority
        ]);
    }


}