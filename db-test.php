<?php

$host = '78.31.235.67';
$port = 3306;

$connection = @fsockopen($host, $port, $errno, $errstr, 10);

header('Content-Type: text/plain; charset=utf-8');

if ($connection) {
    echo "PORT 3306 OPEN\n";
    fclose($connection);
} else {
    echo "PORT 3306 CLOSED\n";
    echo "ERROR: {$errno} - {$errstr}\n";
}