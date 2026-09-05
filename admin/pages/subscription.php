<?php
require '../../config/database.php';

session_start();
if (!isset($_SESSION['admin']))
    exit;

$payments = $pdo->query("
    SELECT * FROM subscriptions
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>


    <svg width="40px" height="40px">
        <use href="#subs"></use>
    </svg>
    اشتراک ها
</h2>

<table class="table-box">

    <tr>
        <th>ID</th>
        <th>کاربر</th>
        <th>شناسه </th>
        <th>پلن</th>
        <th>تاریخ انقضا</th>
        <th> زمان باقیمانده</th>
    </tr>

    <?php foreach ($payments as $p): ?>

        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['user_id'] ?></td>
            <td><?= $p['payment_id'] ?></td>
            <td><?= $p['plan'] ?></td>
            <td><?= $p['expired_at'] ?>

            <td>
                <?php


                if ($p) {
                    $today = new DateTime();
                    $expire = new DateTime($p['expired_at']);

                    if ($today > $expire) {
                        echo " منقضی شده است";
                    } else {
                        $diff = $today->diff($expire);
                        echo $diff->days . " روز";
                    }
                } else {
                    echo "اشتراکی پیدا نشد";
                }

                ?>
            </td>


            </td>
        </tr>

    <?php endforeach; ?>

</table>