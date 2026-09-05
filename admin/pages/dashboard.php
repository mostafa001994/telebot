<?php
require '../../config/database.php';

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth.php");
    exit;
}

// آمارها

$users = $pdo->query("
    SELECT * FROM users
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$paymentsCount = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$activeSubs = $pdo->query("
    SELECT COUNT(*) FROM subscriptions
    WHERE status='active' AND expired_at > NOW()
")->fetchColumn();

$revenue = $pdo->query("
    SELECT SUM(amount) FROM payments WHERE status='paid'
")->fetchColumn();
?>






<?php



?>

<div class="main">

    <header>
        <h1>داشبورد</h1>
    </header>

    <div class="cards">
        <div class="card">
            <h3>کاربران: 👤</h3>
            <p><?= $usersCount ?></p>

            <svg>
                <use href="#users"></use>
            </svg>
        </div>

        <div class="card">
            <h3>اشتراک فعال:</h3>
            <p><?= $activeSubs ?></p>

            <svg>
                <use href="#subs"></use>
            </svg>
        </div>

        <div class="card">
            <h3>درآمد💰</h3>
            <p><?= $paymentsCount ?> خرید /<?= $revenue ?> تومان </p>
            <svg>
                <use href="#credit-cart"></use>
            </svg>
        </div>
    </div>

    <div class="table-box">
        <h3>آخرین کاربران</h3>

        <table>
            <thead>
                <tr>
                    <th>*</th>
                    <th>نام</th>
                    <th>آیدی</th>
                    <th>شماره موبایل</th>
                    <th> جزییات کاربر </th>
                </tr>
            </thead>

            <tbody>
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

            </tbody>
        </table>
    </div>

</div>