<?php
// obschee/config.php
session_start();

// Базовый URL сайта
define('BASE_URL', 'http://sigroup/'); 
// if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
//     define('BASE_URL', 'http://sigroup/');
// } else {
//     // Автоматическое определение для GitHub/hosting
//     $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
//     define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . '/');
// }
// Конфигурация для OpenServer
define('DB_HOST', 'localhost');     
define('DB_NAME', 'sigroup'); 
define('DB_USER', 'root');         
define('DB_PASS', '');        
  
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