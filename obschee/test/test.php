<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Показываем текущие настройки
echo "<h2>PHP Error Settings:</h2>";
echo "error_reporting: " . ini_get('error_reporting') . "<br>";
echo "display_errors: " . ini_get('display_errors') . "<br>";
echo "log_errors: " . ini_get('log_errors') . "<br>";
echo "error_log: " . ini_get('error_log') . "<br>";

// Проверяем projects.php
echo "<h2>Testing projects.php:</h2>";
if (file_exists('projects.php')) {
    echo "File exists<br>";
    
    // Пробуем прочитать файл
    $content = file_get_contents('projects.php');
    if ($content === false) {
        echo "Cannot read file<br>";
    } else {
        echo "File size: " . strlen($content) . " bytes<br>";
        // Показываем первые 100 символов для проверки синтаксиса
        echo "First 100 chars: " . htmlspecialchars(substr($content, 0, 100)) . "...<br>";
    }
} else {
    echo "File NOT found<br>";
}

// Проверяем доступ к базе данных
echo "<h2>Testing Database:</h2>";
try {
    // Вставьте свои реальные данные подключения
    $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTIVE);
    
    // Пробуем получить данные из таблицы projects
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Projects in database: " . $result['count'] . "<br>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Проверяем права доступа
echo "<h2>File Permissions:</h2>";
echo "projects.php permissions: " . substr(sprintf('%o', fileperms('projects.php')), -4) . "<br>";
echo "Directory permissions: " . substr(sprintf('%o', fileperms('.')), -4) . "<br>";
?>