<?php
// Включить все ошибки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Test projects.php - OpenServer</h2>";

// Данные для OpenServer (стандартные)
$host = 'localhost';
$dbname = 'test';  // или ваше имя базы данных
$username = 'root';
$password = '';  // пароль обычно пустой в OpenServer

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTIVE);
    
    echo "Database connected successfully!<br>";
    
    // Показываем все таблицы
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables in database '$dbname':</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Проверяем таблицу projects
    if (in_array('projects', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Projects in database: " . $result['count'] . "</p>";
        
        // Показываем первые 5 проектов
        $stmt = $pdo->query("SELECT id, title, status FROM projects LIMIT 5");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>First " . count($projects) . " projects:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Status</th></tr>";
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td>" . $project['id'] . "</td>";
            echo "<td>" . htmlspecialchars($project['title']) . "</td>";
            echo "<td>" . $project['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>Table 'projects' NOT FOUND in database!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
    
    // Попробуем подключиться без указания базы данных
    echo "<p>Trying to connect without database...</p>";
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTIVE);
        
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Available databases:</h3>";
        echo "<ul>";
        foreach ($databases as $db) {
            echo "<li>$db</li>";
        }
        echo "</ul>";
        
    } catch (PDOException $e2) {
        echo "<p>Cannot connect at all: " . $e2->getMessage() . "</p>";
    }
}

// Проверяем include файлы
echo "<h3>Checking required files:</h3>";
$required_files = [
    'obschee/config.php',
    'obschee/database.php',
    'obschee/functions.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color:green'>✓ $file exists</span><br>";
    } else {
        echo "<span style='color:red'>✗ $file NOT FOUND</span><br>";
    }
}
?>