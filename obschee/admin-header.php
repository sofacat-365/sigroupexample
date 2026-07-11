<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="icon" href="/obschee/assets/img/logo/logo3.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="admin-navbar">
        <div class="container">
            <a href="admin-dashboard.php" class="admin-logo">
                <i class="fas fa-cogs"></i> Админ-панель
            </a>
            
            <div class="admin-user">
                <span>Привет, <?php echo htmlspecialchars($user['username'] ?? 'Администратор'); ?></span>
                <a href="admin-logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </div>
        </div>
    </nav>
    
    <aside class="admin-sidebar">
        <ul class="admin-menu">
            <li><a href="admin_articles.php">
                <i class="fas fa-file-alt"></i> Статьи
            </a></li>
            <li><a href="admin-blog.php">
                <i class="fas fa-blog"></i> Блог
            </a></li>
            <li><a href="admin-data.php">
                <i class="fas fa-database"></i> Данные
            </a></li>
            <li><a href="admin-project.php">
                <i class="fas fa-project-diagram"></i> Проекты
            </a></li>
            <li><a href="../index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i> На сайт
            </a></li>
        </ul>
    </aside>
    
