<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <link rel="icon" href="/obschee/assets/img/logo/logo3.png" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>style.css">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="container">
            <!-- <img src="/obschee/assets/img/logo/logo2.png" alt="test" style="height: 50px; border: 1px solid red;"> -->
   <a href="<?php echo BASE_URL; ?>index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo is_active_page('index'); ?>">
                    <i class="fas fa-home"></i> Главная
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>about.php" class="<?php echo is_active_page('about'); ?>">
                    <i class="fas fa-users"></i> О нас
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>statii.php" class="<?php echo is_active_page('statii'); ?>">
                    <i class="fas fa-file-alt"></i> Статьи
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>data.php" class="<?php echo is_active_page('datas'); ?>">
                    <i class="fas fa-database"></i> Данные
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>blog.php" class="<?php echo is_active_page('blogs'); ?>">
                    <i class="fas fa-blog"></i> Блог
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>projects.php" class="<?php echo is_active_page('projects'); ?>">
                    <i class="fas fa-project-diagram"></i> Проекты
                </a></li>
            </ul>
        </div>
    </nav>