<?php
require '../../config/database.php';

$id = $_GET['id'];

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$id]);
$user = $user->fetch(PDO::FETCH_ASSOC);

$subs = $pdo->prepare("
    SELECT * FROM subscriptions
    WHERE user_id=?
    ORDER BY id DESC
");
$subs->execute([$id]);
$subs = $subs->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>🙋🏻‍♂️ پروفایل کاربر</h1>

<br>

<div class="card user">



    <div >
        <p>نام: <small><?= $user['first_name'] ?> <?= $user['last_name'] ?></small></p>
        <p>یوزرنیم: <small>@<?= $user['username']   ?? 'نامشخص' ?></small></p>
        <p>شماره: <small><?= $user['phone'] ?? '-' ?></small></p>
    </div>


    <div>
        <p>زبان انتخابی: <small><?= $user['language_code'] ?? 'نامشخص'  ?></small></p>
        <p>آخرین بازدید: <small><?= $user['last_seen'] ?? 'نامشخص' ?></small></p>
        <p>آخرین فعالیت: <small><?= $user['updated_at'] ?? '-' ?></small></p>
    </div>

</div>


<br>
<br>


<div class="table-box">

    <h3>🔥 اشتراک‌ها</h3>

    <?php foreach ($subs as $sub): ?>


        <table>
            <thead>
                <tr>
                    <th>*</th>
                    <th>پلن</th>
                    <th>وضعیت</th>
                    <th>پایان</th>
                    <th> مدت زمان باقی مانده </th>
                </tr>
            </thead>

            <tbody>

                <tr>

                    <td>1</td>
                    <td><?= $sub['plan'] ?></td>
                    <td><?= $sub['status'] ?></td>
                    <td><?= $sub['expired_at'] ?></td>
                    <td> <?php

                    if ($sub) {
                        $today = new DateTime();
                        $expire = new DateTime($sub['expired_at']);

                        if ($today > $expire) {
                            echo "اشتراک منقضی شده است";
                        } else {
                            $diff = $today->diff($expire);
                            echo $diff->days . " روز باقی مانده";
                        }
                    } else {
                        echo "اشتراکی پیدا نشد";
                    }

                    ?>

                    </td>

                </tr>


            </tbody>


        <?php endforeach; ?>
</div>