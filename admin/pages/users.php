<?php
require '../../config/database.php';

session_start();
if (!isset($_SESSION['admin'])) {
    exit("Unauthorized");
}

$users = $pdo->query("
    SELECT * FROM users
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>👤 کاربران</h1>

<table class="table-box">

    <tr>
        <th>ID</th>
        <th>نام</th>
        <th>یوزرنیم</th>
        <th>شماره</th>
        <th>عملیات</th>
    </tr>

    <?php foreach ($users as $user): ?>

        <tr>

            <td><?= $user['id'] ?></td>
            <td><?= $user['first_name'] ?></td>
            <td>@<?= $user['username'] ?></td>
            <td><?= $user['phone'] ?? '-' ?></td>

            <td>
                <a href="#user_view?id=<?= $user['id'] ?>" data-page="user_view" data-id="<?= $user['id'] ?>">
                    مشاهده
                </a>
            </td>

        </tr>

    <?php endforeach; ?>

</table>