<?php
// Минимальная рабочая версия
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Данные БД из вашего конфига (посмотрите в obschee/config.php)
$host = 'localhost';
$dbname = 'sig';  // ИЗМЕНИТЕ НА РЕАЛЬНОЕ ИМЯ!
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Простой вывод проектов
    $stmt = $pdo->query("SELECT * FROM projects");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Проекты</h1>";
    foreach ($projects as $project) {
        echo "<h2>" . htmlspecialchars($project['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($project['short_description']) . "</p>";
        echo "<hr>";
    }
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>