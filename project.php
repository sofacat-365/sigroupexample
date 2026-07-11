<?php
$page_title = "Проект";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем ID проекта
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    header('Location: projects.php');
    exit;
}

// Получаем проект
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND status = 'published'");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: projects.php');
    exit;
}

// Увеличиваем просмотры
// $pdo->prepare("UPDATE projects SET views_count = views_count + 1 WHERE id = ?")
//     ->execute([$project_id]);


// Получаем участников проекта
$participants = [];
if ($project['participants']) {
    $participants_json = json_decode($project['participants'], true);
    if (is_array($participants_json) && !empty($participants_json)) {
        $placeholders = str_repeat('?,', count($participants_json) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, position FROM users 
                              WHERE id IN ($placeholders)");
        $stmt->execute($participants_json);
        $participants = $stmt->fetchAll();
    }
}

// Получаем статус проекта
$status_labels = [
    'active' => '🟢 Активный',
    'completed' => '✅ Завершен',
    'planned' => '📅 Планируется',
    'on_hold' => '⏸ Приостановлен'
];
$status_label = isset($status_labels[$project['current_status']]) 
    ? $status_labels[$project['current_status']] 
    : $project['current_status'];
?>

<main class="container">
    <nav class="breadcrumbs">
        <a href="projects.php">Проекты</a> &gt; 
        <span><?= htmlspecialchars($project['title']) ?></span>
    </nav>

    <div class="article-detail">
        <!-- Заголовок и изображение -->
        <div class="article-header">
            <?php if ($project['featured_image_url']): ?>
                <div class="project-image">
                    <img src="<?= htmlspecialchars($project['featured_image_url']) ?>" 
                         alt="<?= htmlspecialchars($project['title']) ?>">
                </div>
            <?php endif; ?>
            
            <h1><?= htmlspecialchars($project['title']) ?></h1>
            
            <!-- Статус и даты -->
            <div class="article-meta">
                <div class="meta-item">
                    <strong>Статус:</strong> <?= $status_label ?>
                </div>
                
                <div class="meta-item">
                    <strong>Начало:</strong>
                    <?= date('d.m.Y', strtotime($project['start_date'])) ?>
                </div>
                
                <?php if ($project['end_date']): ?>
                    <div class="meta-item">
                        <strong>Завершение:</strong>
                        <?= date('d.m.Y', strtotime($project['end_date'])) ?>
                    </div>
                <?php endif; ?>
                
                <!-- <div class="meta-item">
                    <strong>Просмотров:</strong> <?= number_format($project['views_count'], 0, '', ' ') ?>
                </div> -->
            </div>
            
            <!-- Участники -->
            <?php if (!empty($participants)): ?>
                <div class="project-participants">
                    <p class="participants-label">Участники проекта:</p>
                    <div class="participants-list">
                        <?php foreach ($participants as $participant): ?>
                            <div class="participant">
                                <strong><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></strong>
                                <?php if ($participant['position']): ?>
                                    <br><small><?= htmlspecialchars($participant['position']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Краткое описание -->
        <?php if ($project['short_description']): ?>
            <div class="article-abstract">
                <h2>Краткое описание</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['short_description'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Цель проекта -->
        <?php if ($project['goal']): ?>
            <div class="article-abstract">
                <h2>Цель проекта</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['goal'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Задачи -->
        <?php if ($project['tasks']): ?>
            <div class="article-abstract">
                <h2>Задачи проекта</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['tasks'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Полное описание -->
        <?php if ($project['full_description']): ?>
            <div class="article-abstract">
                <h2>Описание проекта</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['full_description'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Результаты -->
        <?php if ($project['results']): ?>
            <div class="article-abstract">
                <h2>Результаты</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['results'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Финансирование -->
        <?php if ($project['funding_info']): ?>
            <div class="article-abstract">
                <h2>Финансирование</h2>
                <div class="abstract-content">
                    <?= nl2br(htmlspecialchars($project['funding_info'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Действия -->
        <div class="article-back">
            <a href="projects.php" class="btn btn-secondary">
                ← К списку проектов
            </a>
            
            <?php if ($project['external_link']): ?>
                <a href="<?= htmlspecialchars($project['external_link']) ?>" 
                   class="btn btn-primary" target="_blank">
                    Официальная страница проекта
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>