<?php
require '../../config/database.php';

session_start();
if (!isset($_SESSION['admin'])) exit;

$payments = $pdo->query("
    SELECT * FROM payments
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>💳 پرداخت‌ها</h1>

<table class="table-box">

<tr>
    <th>ID</th>
    <th>User</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Ref</th>
</tr>

<?php foreach ($payments as $p): ?>

<tr>
    <td><?= $p['id'] ?></td>
    <td><?= $p['user_id'] ?></td>
    <td><?= $p['amount'] ?></td>
    <td><?= $p['status'] ?></td>
    <td><?= $p['ref_id'] ?></td>
</tr>

<?php endforeach; ?>

</table>