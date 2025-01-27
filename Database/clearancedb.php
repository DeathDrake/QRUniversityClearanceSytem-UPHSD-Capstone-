<?php
$mysql_host = 'localhost';
$mysql_db = 'clearancedb';
$mysql_user = 'root';
$mysql_pass = '';

$dsn = "mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $mysql_user, $mysql_pass, $options);
