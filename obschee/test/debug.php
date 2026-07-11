<?php
// Диагностика оригинального projects.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Отладка projects.php</h2>";

// 1. Проверим сам файл
$file = 'projects.php';
echo "<h3>1. Проверка файла projects.php:</h3>";
if (file_exists($file)) {
    echo "✓ Файл существует<br>";
    echo "Размер: " . filesize($file) . " байт<br>";
    
    // Прочитаем первые 30 строк
    $lines = file($file);
    echo "<h4>Первые 30 строк:</h4>";
    echo "<pre style='background:#f0f0f0; padding:10px;'>";
    for ($i = 0; $i < min(30, count($lines)); $i++) {
        echo ($i+1) . ": " . htmlspecialchars($lines[$i]);
    }
    echo "</pre>";
} else {
    echo "✗ Файл не найден";
}

// 2. Проверим требуемые файлы
echo "<h3>2. Проверка include файлов:</h3>";
$includes = [
    'obschee/config.php',
    'obschee/database.php', 
    'obschee/functions.php',
    'obschee/header.php',
    'obschee/footer.php'
];

foreach ($includes as $include) {
    if (file_exists($include)) {
        echo "<span style='color:green'>✓ $include существует</span><br>";
        
        // Проверим конфигурацию БД в config.php
        if ($include == 'obschee/config.php') {
            $content = file_get_contents($include);
            if (strpos($content, 'localhost') !== false && 
                strpos($content, 'root') !== false) {
                echo "<span style='color:green'>  → Настройки БД найдены</span><br>";
            } else {
                echo "<span style='color:orange'>  → Возможно неправильные настройки БД</span><br>";
            }
        }
    } else {
        echo "<span style='color:red'>✗ $include НЕ найден</span><br>";
    }
}

// 3. Попробуем выполнить projects.php по частям
echo "<h3>3. Тестовое выполнение:</h3>";
echo "<h4>a) Подключение конфигурации:</h4>";

if (file_exists('obschee/config.php')) {
    // Временно перехватим вывод
    ob_start();
    try {
        include 'obschee/config.php';
        $output = ob_get_clean();
        echo "<span style='color:green'>✓ config.php выполнен успешно</span><br>";
        
        // Проверим, объявлены ли переменные БД
        $vars = ['db_host', 'db_name', 'db_user', 'db_pass', 'host', 'username', 'password', 'database'];
        $found_vars = [];
        foreach ($vars as $var) {
            if (isset($$var)) {
                $found_vars[] = "$var = " . $$var;
            }
        }
        
        if (!empty($found_vars)) {
            echo "Найденные переменные: " . implode(', ', $found_vars) . "<br>";
        }
        
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo "<span style='color:red'>✗ Ошибка в config.php: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h4>b) Тест подключения к БД:</h4>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sig;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<span style='color:green'>✓ Прямое подключение к БД успешно</span><br>";
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ Ошибка прямого подключения: " . $e->getMessage() . "</span><br>";
}

// 4. Проверим содержание projects.php на наличие ошибок
echo "<h3>4. Поиск проблем в коде projects.php:</h3>";

$content = file_get_contents('projects.php');
$problems = [];

// Ищем типичные проблемы
if (strpos($content, 'mysqli_connect') !== false) {
    $problems[] = "Используется mysqli вместо PDO";
}

if (strpos($content, 'mysql_connect') !== false) {
    $problems[] = "Используется устаревший mysql_connect";
}

if (preg_match('/\$.*=\s*new PDO\([^)]*\)/', $content)) {
    $problems[] = "Найдено создание PDO объекта";
}

// Ищем include/require
preg_match_all('/(include|require)(_once)?\s*[\'"]([^\'"]+\.php)[\'"]/', $content, $matches);
if (!empty($matches[3])) {
    echo "Найдены подключения:<br>";
    foreach ($matches[3] as $include) {
        echo "  - $include<br>";
    }
}

if (!empty($problems)) {
    echo "Возможные проблемы:<br>";
    foreach ($problems as $problem) {
        echo "<span style='color:orange'>⚠ $problem</span><br>";
    }
}

// 5. Проверим доступность через веб
echo "<h3>5. Проверка HTTP доступа:</h3>";
echo "Ссылка на projects.php: <a href='projects.php' target='_blank'>projects.php</a><br>";
echo "Ссылка на тестовую версию: <a href='projects_test_simple.php' target='_blank'>projects_test_simple.php</a><br>";

// 6. Проверим .htaccess на наличие правил для projects.php
echo "<h3>6. Проверка .htaccess:</h3>";
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'projects.php') !== false) {
        echo "<span style='color:orange'>⚠ В .htaccess есть упоминание projects.php</span><br>";
        
        // Покажем строки с projects.php
        $lines = explode("\n", $htaccess);
        foreach ($lines as $num => $line) {
            if (strpos($line, 'projects.php') !== false) {
                echo "Строка " . ($num+1) . ": " . htmlspecialchars($line) . "<br>";
            }
        }
    } else {
        echo "✓ В .htaccess нет специальных правил для projects.php<br>";
    }
} else {
    echo "Файл .htaccess не найден<br>";
}
?>