<?php
require_once 'obschee/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Подключение к БД успешно<br>";
    
    // Проверяем версию MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "✓ Версия MySQL: $version<br>";
    
    // Проверяем таблицы
    $tables = $pdo->query('SHOW TABLES')->fetchAll();
    echo "✓ Найдено таблиц: " . count($tables) . "<br>";
    
    if (count($tables) > 0) {
        echo "Список таблиц:<br>";
        foreach ($tables as $table) {
            echo "- " . reset($table) . "<br>";
        }
    }
    
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>