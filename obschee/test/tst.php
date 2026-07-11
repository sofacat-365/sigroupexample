<?php
// Отключаем ВСЁ лишнее
echo "=== Шаг 1: PHP работает ===<br>";
echo "Версия PHP: " . phpversion() . "<br>";

// Тест №1: Ошибки
echo "<br>=== Шаг 2: Проверка ошибок ===";
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Тест №2: Базовые функции PHP
echo "<br>=== Шаг 3: Базовые функции ===";
if (function_exists('mysqli_connect')) {
    echo "<br>mysqli: ЕСТЬ";
} else {
    echo "<br>mysqli: НЕТУ!";
}

if (class_exists('PDO')) {
    echo "<br>PDO: ЕСТЬ";
} else {
    echo "<br>PDO: НЕТУ!";
}

// Тест №3: Проверяем, может ли PHP писать файлы
echo "<br>=== Шаг 4: Права на запись ===";
$test_file = __DIR__ . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<br>Запись в файл: УСПЕХ";
    unlink($test_file);
} else {
    echo "<br>Запись в файл: НЕ УДАЛОСЬ (права?)";
}

echo "<br><br>=== КОНЕЦ ТЕСТА ===";