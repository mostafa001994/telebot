<?php

class SubscriptionService
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    # -----------------------------
    # دریافت اشتراک فعال
    # -----------------------------
    public function getActive(int $userId)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM subscriptions
            WHERE user_id = ?
              AND status = 'active'
              AND expired_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    # -----------------------------
    # ساخت اشتراک جدید (خرید)
    # -----------------------------
    public function createNew(int $userId, int $paymentId, int $plan): int
    {
        $days = $this->planToDays($plan);

        $start = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime("+$days days"));

        $stmt = $this->db->prepare("
            INSERT INTO subscriptions
            (user_id, payment_id, plan, started_at, expired_at, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ");

        $stmt->execute([
            $userId,
            $paymentId,
            $plan,
            $start,
            $end
        ]);

        return (int)$this->db->lastInsertId();
    }

    # -----------------------------
    # تمدید اشتراک (اضافه به زمان فعلی)
    # -----------------------------
    public function renew(int $userId, int $plan): bool
    {
        $active = $this->getActive($userId);

        $days = $this->planToDays($plan);

        $baseDate = $active
            ? $active['expired_at']
            : date('Y-m-d H:i:s');

        $newExpire = date(
            'Y-m-d H:i:s',
            strtotime($baseDate . " +$days days")
        );

        if ($active) {

            $stmt = $this->db->prepare("
                UPDATE subscriptions
                SET expired_at = ?, updated_at = NOW()
                WHERE id = ?
            ");

            return $stmt->execute([
                $newExpire,
                $active['id']
            ]);
        }

        // اگر اشتراک نداشت → ساخت جدید
        return $this->createSimple($userId, $plan, $newExpire);
    }

    # -----------------------------
    # ارتقا اشتراک (بدون از دست رفتن زمان)
    # -----------------------------
    public function upgrade(int $userId, int $newPlan): bool
    {
        $active = $this->getActive($userId);

        $newDays = $this->planToDays($newPlan);

        if (!$active) {
            return $this->createSimple(
                $userId,
                $newPlan,
                date('Y-m-d H:i:s', strtotime("+$newDays days"))
            );
        }

        $remainingSeconds =
            strtotime($active['expired_at']) - time();

        $remainingDays = max(0, ceil($remainingSeconds / 86400));

        $totalDays = $remainingDays + $newDays;

        $newExpire = date(
            'Y-m-d H:i:s',
            strtotime("+$totalDays days")
        );

        $stmt = $this->db->prepare("
            UPDATE subscriptions
            SET plan = ?, expired_at = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $newPlan,
            $newExpire,
            $active['id']
        ]);
    }

    # -----------------------------
    # Expire خودکار
    # -----------------------------
    public function expireOld(): int
    {
        $stmt = $this->db->prepare("
            UPDATE subscriptions
            SET status = 'expired'
            WHERE expired_at <= NOW()
              AND status = 'active'
        ");

        $stmt->execute();

        return $stmt->rowCount();
    }

    # -----------------------------
    # Helper
    # -----------------------------
    private function planToDays(int $plan): int
    {
        return match ($plan) {
            1 => 30,
            3 => 90,
            12 => 365,
            default => 30
        };
    }

    private function createSimple(int $userId, int $plan, string $expire)
    {
        $stmt = $this->db->prepare("
            INSERT INTO subscriptions
            (user_id, payment_id, plan, started_at, expired_at, status)
            VALUES (?, 0, ?, NOW(), ?, 'active')
        ");

        return $stmt->execute([
            $userId,
            $plan,
            $expire
        ]);
    }
}