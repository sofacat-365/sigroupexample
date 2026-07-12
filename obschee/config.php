<?php
// obschee/config.php
session_start();

// Базовый URL сайта
// devtunnels/ngrok проксируют запрос на localhost, а настоящий публичный хост
// кладут в X-Forwarded-Host — поэтому он приоритетнее обычного Host.
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'sigroup';
$host = explode(':', $host)[0]; // отбрасываем порт (localhost:80 -> localhost)
if ($host === 'sigroup' || $host === 'localhost' || $host === '127.0.0.1') {
    define('BASE_URL', 'http://sigroup/');
} else {
    $isHttps = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $protocol = $isHttps ? 'https://' : 'http://';
    define('BASE_URL', $protocol . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $host) . '/');
}
// Конфигурация БД: переменные окружения (Docker) с фолбэком на OpenServer по умолчанию
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'sigroup');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
  
// Настройки сайта
define('SITE_NAME', 'Научная группа');
define('SITE_TITLE', 'Научно-исследовательская группа');
define('SITE_DESCRIPTION', 'Сайт нашей исследовательской команды');
define('ADMIN_EMAIL', 'admin@localhost');

// Пути к папкам - ВАЖНО: config.php в папке obschee!
define('ROOT_PATH', dirname(__DIR__)); // Поднимаемся на уровень выше
define('OBSCH_PATH', __DIR__); // Путь к папке obschee
define('UPLOAD_PATH', ROOT_PATH . '/upload/');
define('ADMIN_PATH', ROOT_PATH . '/admin/');
define('ASSETS_URL', BASE_URL . 'obschee/assets/');
define('CSS_URL', ASSETS_URL . 'css/');

// Настройки безопасности
define('SESSION_TIMEOUT', 7200);
define('CSRF_TOKEN_LIFE', 3600);

// Настройки загрузки файлов
define('MAX_FILE_SIZE', 10 * 1024 * 1024);
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt']);

// Проверяем существование папки загрузок
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Функция для безопасного вывода
function safe_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Функция для проверки активной страницы
function is_active_page($page_name) {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    return $current_page == $page_name ? 'active' : '';
}
?>