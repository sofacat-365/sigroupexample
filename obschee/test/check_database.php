<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Базы данных на OpenServer:</h2>";
    
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li><strong>$db</strong> - ";
        
        // Покажем таблицы в каждой базе
        try {
            $pdo2 = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $username, $password);
            $tables_stmt = $pdo2->query("SHOW TABLES");
            $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (in_array('projects', $tables)) {
                echo "<span style='color:green'>✓ Содержит таблицу 'projects' (" . count($tables) . " таблиц)</span>";
            } else {
                echo count($tables) . " таблиц";
            }
        } catch (Exception $e) {
            echo "Ошибка доступа";
        }
        
        echo "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}

// Проверим config.php
echo "<h2>Проверка config.php:</h2>";
if (file_exists('obschee/config.php')) {
    $config_content = file_get_contents('obschee/config.php');
    
    // Ищем параметры БД
    preg_match_all('/\$.*db.*=.*[\'"]([^\'"]*)[\'"]/', $config_content, $matches);
    
    echo "Найдены параметры БД:<br>";
    foreach ($matches[0] as $match) {
        echo htmlspecialchars($match) . "<br>";
    }
    
    // Выделим часть файла с настройками БД
    if (preg_match('/\$.*db.*=.*[\'"].*[\'"].*\$/s', $config_content, $config_match)) {
        echo "<pre>" . htmlspecialchars($config_match[0]) . "</pre>";
    }
} else {
    echo "config.php не найден";
}
?>