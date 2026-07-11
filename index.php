<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Главная | Центр роботизированных решений";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';
try {
    $stmt = $pdo->query("SELECT * FROM articles 
                         WHERE status = 'published' 
                         ORDER BY publication_date DESC 
                         LIMIT 3");
    $latest_articles = $stmt->fetchAll();
    $stmt = $pdo->query("SELECT * FROM projects 
                         WHERE current_status = 'active' 
                         ORDER BY start_date DESC 
                         LIMIT 4");
    $active_projects = $stmt->fetchAll();
      $stmt = $pdo->query("SELECT * FROM blog_posts 
                         WHERE status = 'published' 
                         ORDER BY published_at DESC 
                         LIMIT 3");
    $latest_posts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Ошибка загрузки данных: " . $e->getMessage();
}
?>
<section class="welcome">
    <h1>Центр роботизированных решений</h1>
    <p class="mission-statement">
        <strong>Наша миссия:</strong> Разрабатывать доступных, полезных и интуитивно понятных роботизированных помощников для решения повседневных задач, улучшения качества жизни и автоматизации рутинной научной работы.
    </p>
    <div>
        <a href="about.php" class="btn btn-secondary">О команде</a>
    </div>
</section>
<section class="latest-section">
    <h2>Наши ключевые проекты</h2>
    <div class="projects-grid">
        <?php foreach ($active_projects as $project): ?>
        <div class="article-card">
            <h3>
                <?= htmlspecialchars($project['title']) ?>
            </h3>
            <p>
                <?= htmlspecialchars(substr($project['short_description'] ?? 'Описание проекта', 0, 120)) ?>...
            </p>
            <a href="projects.php?id=<?= $project['id'] ?? '#' ?>" class="btn">Подробнее</a>
        </div>
        <?php endforeach; ?>
    </div>
    <div>
        <a href="projects.php" class="btn">Все проекты</a>
    </div>
</section>
<section class="latest-section">
    <h2>Последние научные публикации</h2>
    <div class="articles-grid">
        <?php if (!empty($latest_articles)): ?>
            <?php foreach ($latest_articles as $article): ?>
            <div class="article-card">
                <h3>
                    <a href="statii.php?id=<?= $article['id'] ?>">
                        <?= htmlspecialchars($article['title']) ?>
                    </a>
                </h3>
                <div class="meta">
                    <span class="date">
                        <?= date('d.m.Y', strtotime($article['publication_date'])) ?>
                    </span>
                    <?php if ($article['journal_name']): ?>
                        <span class="journal"><?= htmlspecialchars($article['journal_name']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="abstract"><?= htmlspecialchars(substr($article['abstract'], 0, 150)) ?>...</p>
                <div class="article-actions">
                    <a href="statiya.php?id=<?= $article['id'] ?>" class="btn">Подробнее</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="article-card">
                <p>Скоро здесь появятся наши публикации. Следите за обновлениями!</p>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <a href="statii.php" class="btn">Все статьи</a>
    </div>
</section>
<section class="latest-section">
    <h2>Новости и события</h2>
    <div class="blog-grid">
        <?php if (!empty($latest_posts)): ?>
            <?php foreach ($latest_posts as $post): ?>
            <div class="post-card">
              
                <div class="post-content">
                    <div class="post-meta">
                        <span><?= date('d.m.Y', strtotime($post['published_at'])) ?></span>
                        <span><?= $post['views_count'] ?? 0 ?> просмотров</span>
                    </div>
                    <h3 class="post-title">
                        <a href="blog.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h3>
                    <p class="post-excerpt"><?= htmlspecialchars(substr($post['excerpt'] ?? $post['content'], 0, 120)) ?>...</p>
                    <a href="blog.php?id=<?= $post['id'] ?>" class="btn">Подробнее</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="post-card">
                <div class="post-content">
                    <h3 class="post-title">Добро пожаловать в наш блог!</h3>
                    <p class="post-excerpt">Здесь мы будем делиться новостями о наших проектах, событиями и мыслями о робототехнике.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <a href="blogs.php" class="btn">Все новости</a>
    </div>
</section>

<?php
require_once 'obschee/footer.php';
?>