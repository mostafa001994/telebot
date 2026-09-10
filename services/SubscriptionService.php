<?php

class SubscriptionService
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Get Active Subscriptions

    public function getActive(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,

                sp.name AS plan_name,
                sp.duration_days,
                sp.price,
                sp.discount_price,

                sc.name AS category_name

            FROM subscriptions s

            LEFT JOIN subscription_plans sp
                ON sp.id = s.plan_id

            LEFT JOIN subscription_categories sc
                ON sc.id = sp.category_id

            WHERE s.user_id = ?
              AND s.status = 'active'
              AND s.expired_at > NOW()

            ORDER BY s.id DESC

            LIMIT 1
        ");

        $stmt->execute([
            $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Get User Subscriptions

    public function getUserSubscriptions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,

                sp.name AS plan_name,
                sp.duration_days,
                sp.price,

                sc.name AS category_name

            FROM subscriptions s

            LEFT JOIN subscription_plans sp
                ON sp.id = s.plan_id

            LEFT JOIN subscription_categories sc
                ON sc.id = sp.category_id

            WHERE s.user_id = ?

            ORDER BY s.id DESC
        ");

        $stmt->execute([
            $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create New Subscriptions After Pay

    public function createNew(
        int $userId,
        int $paymentId,
        int $planId
    ): int {
        $stmt = $this->db->prepare("
            SELECT
                id,
                duration_days
            FROM subscription_plans
            WHERE id = ?
              AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            $planId
        ]);

        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            throw new Exception(
                'Subscription plan not found.'
            );
        }

        $days = (int) $plan['duration_days'];

        $start = date('Y-m-d H:i:s');

        $end = date(
            'Y-m-d H:i:s',
            strtotime("+{$days} days")
        );

        $stmt = $this->db->prepare("
            INSERT INTO subscriptions
            (
                user_id,
                payment_id,
                plan_id,
                plan,
                started_at,
                expired_at,
                status
            )
            VALUES (?, ?, ?, 0, ?, ?, 'active')
        ");

        $stmt->execute([
            $userId,
            $paymentId,
            $planId,
            $start,
            $end
        ]);

        return (int) $this->db->lastInsertId();
    }


    // Renew Subscription

    public function renew(
        int $userId,
        int $planId
    ): bool {
        $active = $this->getActive($userId);

        $days = $this->getPlanDuration(
            $planId
        );

        $baseDate = $active
            ? $active['expired_at']
            : date('Y-m-d H:i:s');

        $newExpire = date(
            'Y-m-d H:i:s',
            strtotime($baseDate . " +{$days} days")
        );

        if ($active) {

            $stmt = $this->db->prepare("
                UPDATE subscriptions

                SET expired_at = ?,
                    plan_id = ?,
                    updated_at = NOW()

                WHERE id = ?
            ");

            return $stmt->execute([
                $newExpire,
                $planId,
                $active['id']
            ]);
        }

        return $this->createSimple(
            $userId,
            $planId,
            $newExpire
        );
    }

    // Upgrade Subscription

    public function upgrade(
        int $userId,
        int $newPlanId
    ): bool {
        $active = $this->getActive($userId);

        $newDays = $this->getPlanDuration(
            $newPlanId
        );

        if (!$active) {

            return $this->createSimple(
                $userId,
                $newPlanId,
                date(
                    'Y-m-d H:i:s',
                    strtotime("+{$newDays} days")
                )
            );
        }

        $remainingSeconds =
            strtotime($active['expired_at']) - time();

        $remainingDays = max(
            0,
            (int) ceil(
                $remainingSeconds / 86400
            )
        );

        $totalDays =
            $remainingDays + $newDays;

        $newExpire = date(
            'Y-m-d H:i:s',
            strtotime("+{$totalDays} days")
        );

        $stmt = $this->db->prepare("
            UPDATE subscriptions

            SET plan_id = ?,
                expired_at = ?,
                updated_at = NOW()

            WHERE id = ?
        ");

        return $stmt->execute([
            $newPlanId,
            $newExpire,
            $active['id']
        ]);
    }


    // Expire Old Subscription

    public function expireOld(): int
    {
        $stmt = $this->db->prepare("
            UPDATE subscriptions

            SET status = 'expired',
                updated_at = NOW()

            WHERE expired_at <= NOW()
              AND status = 'active'
        ");

        $stmt->execute();

        return $stmt->rowCount();
    }


    // GetPlanDuration

    private function getPlanDuration(
        int $planId
    ): int {
        $stmt = $this->db->prepare("
            SELECT duration_days

            FROM subscription_plans

            WHERE id = ?
              AND status = 'active'

            LIMIT 1
        ");

        $stmt->execute([
            $planId
        ]);

        $days = $stmt->fetchColumn();

        if (!$days) {
            throw new Exception(
                'Subscription plan not found.'
            );
        }

        return (int) $days;
    }

    
    // ساخت اشتراک بدون پرداخت
    // فعلاً برای سازگاری با سیستم قبلی نگه داشته شده.

    private function createSimple(
        int $userId,
        int $planId,
        string $expire
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO subscriptions
            (
                user_id,
                payment_id,
                plan_id,
                plan,
                started_at,
                expired_at,
                status
            )
            VALUES (?, 0, ?, 0, NOW(), ?, 'active')
        ");

        return $stmt->execute([
            $userId,
            $planId,
            $expire
        ]);
    }

}