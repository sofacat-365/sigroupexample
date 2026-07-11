<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "✅ Вы вошли как: " . $_SESSION['username'] . " (роль: " . $_SESSION['role'] . ")";
} else {
    echo "❌ Вы не авторизованы";
}
?>