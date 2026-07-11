<?php
require_once 'config.php';

try {
    // Подключаемся к MySQL
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Если ошибка - показываем понятное сообщение
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
