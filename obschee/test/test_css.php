<?php
// test-css.php
// config.php находится в папке obschee
require_once 'obschee/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест подключения CSS</title>
    
    <!-- Проверяем пути -->
    <style>
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
        }
        .debug-info code {
            background: #e9ecef;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
    
    <!-- ТОЛЬКО внешний CSS файл -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>style.css">
</head>
<body>
    <div class="container">
        <h1>Тест подключения CSS файла</h1>
        
        <div class="debug-info">
            <h3>Отладочная информация:</h3>
            <p>BASE_URL: <code><?php echo BASE_URL; ?></code></p>
            <p>CSS_URL: <code><?php echo CSS_URL; ?></code></p>
            <p>Путь к CSS: <code><?php echo CSS_URL; ?>style.css</code></p>
            <p>Проверить: <a href="<?php echo CSS_URL; ?>style.css" target="_blank">открыть CSS файл</a></p>
        </div>
        
        <!-- Тестируем стили из CSS файла -->
        <div class="test-box" style="background: #e74c3c; color: white; padding: 20px; margin: 20px 0; text-align: center;">
            Этот блок имеет INLINE стили (красный). Если ниже есть стилизованные блоки - CSS работает.
        </div>
        
        <div class="article-card">
            Этот блок должен быть стилизован через CSS файл
        </div>
        
        <button class="test-button">Тестовая кнопка</button>
        
        <div class="article-card">
            <h2>Карточка</h2>
            <p>Если эта карточка имеет стили - CSS работает</p>
        </div>
        
        <h2>Что проверять:</h2>
        <ol>
            <li>Откройте <a href="<?php echo CSS_URL; ?>style.css" target="_blank">CSS файл</a> в новой вкладке</li>
            <li>Если видите код CSS - путь правильный</li>
            <li>Нажмите F12 → Network → посмотрите запрос к style.css</li>
        </ol>
    </div>
    
    <script>
    // Простая проверка в JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        var testBox = document.querySelector('.test-box:not([style])');
        var computedStyle = window.getComputedStyle(testBox);
        
        console.log('Проверка CSS:');
        console.log('Фоновый цвет (должен быть градиент):', computedStyle.background);
        console.log('Цвет текста:', computedStyle.color);
        console.log('Путь к CSS:', '<?php echo CSS_URL; ?>style.css');
    });
    </script>
</body>
</html>