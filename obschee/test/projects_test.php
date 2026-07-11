<?php
// Упрощенная версия projects.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Проекты";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1><?php echo $page_title; ?></h1>
    
    <?php
    echo "<h3>Проверка подключения к БД:</h3>";
    
    // Попробуем разные имена баз данных
    $possible_dbs = ['sig', 'test', 'database', 'projects_db', 'my_site'];
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $connected = false;
    
    foreach ($possible_dbs as $dbname) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<p class='success'>✓ Подключено к базе данных: <strong>$dbname</strong></p>";
            
            // Проверим таблицу projects
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Найдено проектов: " . $result['count'] . "</p>";
            
            // Покажем проекты
            $stmt = $pdo->query("SELECT id, title, current_status FROM projects ORDER BY id LIMIT 10");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($projects) > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Название</th><th>Статус</th></tr>";
                foreach ($projects as $project) {
                    echo "<tr>";
                    echo "<td>" . $project['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($project['title']) . "</td>";
                    echo "<td>" . $project['current_status'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            $connected = true;
            break;
            
        } catch (PDOException $e) {
            echo "<p>✗ Не удалось подключиться к $dbname: " . $e->getMessage() . "</p>";
        }
    }
    
    if (!$connected) {
        echo "<p class='error'>Не удалось подключиться ни к одной базе данных.</p>";
        echo "<p>Пожалуйста, проверьте:</p>";
        echo "<ul>";
        echo "<li>Запущен ли MySQL в OpenServer</li>";
        echo "<li>Настройки в файле obschee/config.php</li>";
        echo "<li>Имя базы данных в PHPMyAdmin</li>";
        echo "</ul>";
    }
    ?>
    
    <hr>
    <h3>Информация о системе:</h3>
    <p>PHP Version: <?php echo phpversion(); ?></p>
    <p>Current Directory: <?php echo __DIR__; ?></p>
    <p>OpenServer Directory: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Не определен'; ?></p>
</body>
</html>