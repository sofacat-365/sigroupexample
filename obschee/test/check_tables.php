<?php
// check_tables.php
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
    
    echo "<h2>Проверка базы данных</h2>";
    
    // Проверяем все таблицы
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    
    echo "<h3>Найдено таблиц: " . count($tables) . "</h3>";
    
    foreach ($tables as $table) {
        $table_name = $table['Tables_in_' . DB_NAME];
        echo "<p>Таблица: <strong>$table_name</strong></p>";
        
        // Проверяем есть ли таблица projects
        if ($table_name === 'projects') {
            echo "<div style='color:green;'>✓ Таблица projects найдена</div>";
            
            // Проверяем структуру
            $columns = $pdo->query("DESCRIBE projects")->fetchAll();
            echo "<h4>Структура таблицы projects:</h4>";
            echo "<ul>";
            foreach ($columns as $col) {
                echo "<li>{$col['Field']} ({$col['Type']})</li>";
            }
            echo "</ul>";
            
            // Проверяем есть ли записи
            $count = $pdo->query("SELECT COUNT(*) as cnt FROM projects")->fetch()['cnt'];
            echo "<p>Записей в таблице: $count</p>";
            
            if ($count > 0) {
                $projects = $pdo->query("SELECT id, title FROM projects LIMIT 5")->fetchAll();
                echo "<h4>Первые 5 проектов:</h4>";
                foreach ($projects as $proj) {
                    echo "<p>ID: {$proj['id']} - {$proj['title']}</p>";
                }
            }
        }
    }
    
    // Если таблицы projects нет
    if (!in_array('projects', array_column($tables, 'Tables_in_' . DB_NAME))) {
        echo "<div style='color:red;'>✗ Таблица projects не найдена!</div>";
        echo "<p>Нужно создать таблицу:</p>";
        echo "<pre>";
        echo "CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    short_description TEXT NOT NULL,
    full_description LONGTEXT NOT NULL,
    status ENUM('published', 'draft') DEFAULT 'draft',
    start_date DATE NOT NULL,
    end_date DATE,
    current_status ENUM('active', 'completed', 'planned', 'on_hold') DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>