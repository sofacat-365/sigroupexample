<?php
// projects_fixed.php - исправленная версия
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Проекты";

// Подключение к БД
$host = 'localhost';
$dbname = 'sig';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// HTML шапка
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SIG</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .project { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .project-title { color: #2c3e50; margin-top: 0; }
        .status-active { color: green; }
        .status-completed { color: blue; }
        .status-planned { color: orange; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        
        <?php
        // Получаем проекты
        try {
            $stmt = $pdo->query("
                SELECT id, title, short_description, full_description, 
                       current_status, start_date, end_date, funding_source, budget
                FROM projects 
                WHERE status = 'published'
                ORDER BY start_date DESC
            ");
            
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($projects) > 0) {
                foreach ($projects as $project) {
                    echo '<div class="project">';
                    echo '<h2 class="project-title">' . htmlspecialchars($project['title']) . '</h2>';
                    
                    if (!empty($project['short_description'])) {
                        echo '<p><strong>Краткое описание:</strong> ' . htmlspecialchars($project['short_description']) . '</p>';
                    }
                    
                    echo '<p><strong>Статус:</strong> <span class="status-' . $project['current_status'] . '">';
                    switch ($project['current_status']) {
                        case 'active': echo 'Активный'; break;
                        case 'completed': echo 'Завершен'; break;
                        case 'planned': echo 'Планируется'; break;
                        case 'on_hold': echo 'Приостановлен'; break;
                        default: echo $project['current_status'];
                    }
                    echo '</span></p>';
                    
                    if ($project['start_date']) {
                        echo '<p><strong>Начало:</strong> ' . $project['start_date'] . '</p>';
                    }
                    
                    if ($project['end_date']) {
                        echo '<p><strong>Окончание:</strong> ' . $project['end_date'] . '</p>';
                    }
                    
                    if ($project['funding_source']) {
                        echo '<p><strong>Источник финансирования:</strong> ' . htmlspecialchars($project['funding_source']) . '</p>';
                    }
                    
                    if ($project['budget']) {
                        echo '<p><strong>Бюджет:</strong> ' . number_format($project['budget'], 2, ',', ' ') . ' руб.</p>';
                    }
                    
                    echo '</div>';
                }
            } else {
                echo '<p>Нет опубликованных проектов.</p>';
            }
            
        } catch (PDOException $e) {
            echo '<p style="color:red">Ошибка при получении проектов: ' . $e->getMessage() . '</p>';
        }
        ?>
        
        <hr>
        <p><small>Всего проектов: <?php echo count($projects); ?></small></p>
        <p><a href="projects_test_simple.php">Тестовая версия</a> | <a href="debug_projects.php">Отладка</a></p>
    </div>
</body>
</html>