<?php
$page_title = "Блог";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем ID записи блога
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header('Location: blogs.php');
    exit;
}

// Получаем запись блога
$stmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name
    FROM blog_posts bp
    LEFT JOIN users u ON bp.author_id = u.id
    WHERE bp.id = ? AND bp.status = 'published'
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blogs.php');
    exit;
}

// Увеличиваем просмотры
$pdo->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ?")
    ->execute([$post_id]);

// Декодируем теги
$tags = [];
if ($post['tags']) {
    $tags_json = json_decode($post['tags'], true);
    if (is_array($tags_json)) {
        $tags = $tags_json;
    }
}
?>

<main class="container">
    <!-- Хлебные крошки -->
    <nav class="breadcrumbs" style="margin-bottom: 2rem; padding: 0.5rem 0; color: var(--gray);">
        <a href="blogs.php" style="color: var(--primary-color); text-decoration: none;">Блог и новости</a> 
        <span style="color: var(--gray);"> &gt; </span>
        <span style="color: var(--text-light);"><?= htmlspecialchars($post['title']) ?></span>
    </nav>

    <!-- Детальная страница записи блога -->
    <div class="article-detail">
        <!-- Заголовок -->
        <div class="article-header">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            
            <!-- Мета-информация (как на странице статьи) -->
            <div class="article-meta" style="display: flex; flex-wrap: wrap; gap: 2rem; margin-top: 1rem; padding: 1rem; background: var(--light-gray); border-radius: var(--border-radius);">
                <div class="meta-item">
                    <strong>Дата публикации:</strong>
                    <?= $post['published_at'] ? date('d.m.Y', strtotime($post['published_at'])) : 'Не указана' ?>
                </div>
                
                <?php if ($post['first_name']): ?>
                    <div class="meta-item">
                        <strong>Автор:</strong> 
                        <span style="color: var(--primary-color);"><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="meta-item">
                    <strong>Просмотров:</strong> 
                    <span style="color: var(--primary-color);"><?= number_format($post['views_count'], 0, '', ' ') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Изображение записи -->
  
        
        <!-- Теги (стилизованы как на странице блога) -->
        <?php if (!empty($tags)): ?>
            <div style="margin: 2rem 0;">
                <div class="post-tags" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                    <strong style="color: var(--secondary-color);">Теги:</strong>
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag" style="background: var(--light-gray); padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.8rem; color: var(--text-light);">
                            <?= htmlspecialchars($tag) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        

        
        <!-- Полный текст (как контент статьи) -->
        <div class="article-content" style="margin: 2.5rem 0;">
            <div class="content" style="color: var(--text-color); line-height: 1.8; font-size: 1.1rem;">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </div>
        
        <!-- Действия (кнопка возврата) -->
        <div class="article-back" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--light-gray); text-align: center;">
            <a href="blogs.php" class="btn btn-secondary">
                ← К списку записей
            </a>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>