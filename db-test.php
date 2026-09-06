<?php

header('Content-Type: text/plain; charset=utf-8');

$host = '78.31.235.67';
$port = 3306;
$db   = 'bot';
$user = 'telebot';
$pass = 'Javad10102020@';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
        ]
    );

    echo "DATABASE CONNECTION: OK\n";

    $result = $pdo->query("SELECT DATABASE() AS db, VERSION() AS version")->fetch(PDO::FETCH_ASSOC);

    echo "DATABASE: " . $result['db'] . "\n";
    echo "MYSQL VERSION: " . $result['version'] . "\n";

} catch (Throwable $e) {
    echo "DATABASE CONNECTION: FAILED\n";
    echo "ERROR: " . $e->getMessage() . "\n";
}