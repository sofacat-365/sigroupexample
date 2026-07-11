<?php
$page_title = "Статья";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем ID статьи
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header('Location: articles.php');
    exit;
}

// Получаем статью
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND status = 'published'");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: articles.php');
    exit;
}

// Увеличиваем просмотры
$pdo->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = ?")
    ->execute([$article_id]);

// Получаем ВСЕХ авторов из таблицы users для отображения
$stmt = $pdo->query("SELECT id, first_name, last_name, position FROM users WHERE is_active = 1 ORDER BY last_name, first_name");
$all_authors = $stmt->fetchAll();
?>

<main class="container">
    <nav class="breadcrumbs">
        <a href="statii.php">Статьи</a> &gt; 
        <span><?= htmlspecialchars($article['title']) ?></span>
    </nav>

    <div class="article-detail">
        <!-- Заголовок -->
        <div class="article-header">
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            
            <!-- Авторы - показываем всех пользователей -->
            <?php if (!empty($all_authors)): ?>
                <div class="article-authors">
                    <p class="authors-label">Научный коллектив:</p>
                    <p class="authors-list">
                        <?php 
                        $author_names = [];
                        foreach ($all_authors as $author) {
                            $author_names[] = htmlspecialchars($author['first_name'] . ' ' . $author['last_name']);
                        }
                        echo implode(', ', $author_names);
                        ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Мета-информация -->
            <div class="article-meta">
                <div class="meta-item">
                    <strong>Дата публикации:</strong>
                    <?= date('d.m.Y', strtotime($article['publication_date'])) ?>
                </div>
                
                <?php if ($article['journal_name']): ?>
                    <div class="meta-item">
                        <strong>Журнал:</strong> <?= htmlspecialchars($article['journal_name']) ?>
                    </div>
                <?php endif; ?>
                
                <div class="meta-item">
                    <strong>Просмотров:</strong> <?= number_format($article['views_count'], 0, '', ' ') ?>
                </div>
            </div>
        </div>
        
        <div class="article-content">
            <h2>Текст статьи</h2>
            <div class="content">
                <?= nl2br(htmlspecialchars($article['content'])) ?>
            </div>
        </div>
        
        <!-- Действия -->
        <div class="article-actions">
            <a href="statii.php" class="btn">
                ← К списку статей
            </a>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>