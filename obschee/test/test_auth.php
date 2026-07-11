<?php
require_once 'obschee/config.php';
require_once 'obschee/db.php';
require_once 'obschee/auth.php';

echo "1. Auth loaded: " . (class_exists('Auth') ? 'YES' : 'NO') . "<br>";
echo "2. Current user: " . ($auth->getCurrentUser() ? 'Logged in' : 'Not logged in') . "<br>";

// Проверка существования администратора
$admin = $auth->db->fetchOne("SELECT * FROM users WHERE role = 'admin'");
if ($admin) {
    echo "3. Admin exists: YES (username: {$admin['username']})<br>";
    
    // Тест входа
    echo "4. Test login: ";
    $test = $auth->login($admin['username'], 'WRONG_PASSWORD');
    echo $test['success'] ? 'SUCCESS' : 'FAIL: ' . $test['message'];
} else {
    echo "3. Admin exists: NO<br>";
}
?>