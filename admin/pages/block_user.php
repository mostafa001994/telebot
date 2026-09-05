<?php
require '../../config/database.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("
    UPDATE users SET status=0 WHERE id=?
");

$stmt->execute([$id]);

header("Location: ../users.php");