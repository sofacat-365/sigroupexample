<?php
$page_title = "Статьи | Центр роботизированных решений";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Проверяем, запрошена ли конкретная статья
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id > 0) {
    // Показываем конкретную статью
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND status = 'published'");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if ($article) {
        // Увеличиваем счетчик просмотров
        $pdo->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = ?")
            ->execute([$article_id]);
        
        // Получаем авторов из таблицы users
        $author_names = [];
        if ($article['authors']) {
            $authors_json = json_decode($article['authors'], true);
            if (is_array($authors_json) && !empty($authors_json)) {
                $author_ids = array_map('intval', $authors_json);
                if (!empty($author_ids)) {
                    $placeholders = str_repeat('?,', count($author_ids) - 1) . '?';
                    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id IN ($placeholders)");
                    $stmt->execute($author_ids);
                    $authors_data = $stmt->fetchAll();
                    
                    foreach ($authors_data as $author) {
                        $author_names[] = htmlspecialchars($author['first_name'] . ' ' . $author['last_name']);
                    }
                }
            }
        }
        ?>
        
        <main class="container">
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            
            <div class="article-detail">
                <div class="article-header">
                    <!-- Авторы -->
                    <?php if (!empty($author_names)): ?>
                        <div class="article-authors">
                            <p class="authors-label">Авторы:</p>
                            <p class="authors-list">
                                <?= implode(', ', $author_names) ?>
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
                                <strong>Журнал:</strong>
                                <?= htmlspecialchars($article['journal_name']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($article['doi']): ?>
                            <div class="meta-item">
                                <strong>DOI:</strong>
                                <?= htmlspecialchars($article['doi']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <strong>Просмотров:</strong>
                            <?= number_format($article['views_count'], 0, '', ' ') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Аннотация -->
                <div class="article-abstract">
                    <h2>Аннотация</h2>
                    <div class="abstract-content">
                        <?= nl2br(htmlspecialchars($article['abstract'])) ?>
                    </div>
                </div>
                
                <!-- Полный текст -->
                <div class="article-content">
                    <h2>Текст статьи</h2>
                    <div class="content">
                        <?= nl2br(htmlspecialchars($article['content'])) ?>
                    </div>
                </div>
                
                <!-- Кнопка назад -->
                <div class="article-actions">
                    <a href="statii.php" class="btn">
                        ← К списку статей
                    </a>
                </div>
            </div>
        </main>
        
        <?php
    } else {
        // Статья не найдена
        ?>
        <main class="container">
            <div class="no-data">
                <p>Статья не найдена</p>
                <a href="statii.php" class="btn">Вернуться к списку</a>
            </div>
        </main>
        <?php
    }
} else {
    // Показываем список статей
    // Получаем номер страницы из URL
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Получаем общее количество статей
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
    $total_articles = $stmt->fetch()['total'];
    $total_pages = ceil($total_articles / $per_page);

    // Получаем статьи для текущей страницы
    $stmt = $pdo->prepare("SELECT * FROM articles 
                          WHERE status = 'published' 
                          ORDER BY publication_date DESC 
                          LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll();
?>

<main class="container">
    <h1>Научные статьи</h1>
    
    <?php if (!empty($articles)): ?>
        <!-- Краткое описание раздела -->
        <div class="team-description" style="margin-bottom: 2rem;">
            <p>В этом разделе представлены научные статьи и публикации нашей исследовательской группы.</p>
        </div>
        
        <div class="datasets-container">
            <div class="datasets-grid">
                <?php foreach ($articles as $article): ?>
                <?php 
                // Получаем имена авторов для каждой статьи
                $author_names = [];
                if ($article['authors']) {
                    $authors_json = json_decode($article['authors'], true);
                    if (is_array($authors_json) && !empty($authors_json)) {
                        $author_ids = array_map('intval', $authors_json);
                        if (!empty($author_ids)) {
                            $placeholders = str_repeat('?,', count($author_ids) - 1) . '?';
                            $stmt2 = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id IN ($placeholders)");
                            $stmt2->execute($author_ids);
                            $authors_data = $stmt2->fetchAll();
                            
                            foreach ($authors_data as $author) {
                                $author_names[] = htmlspecialchars($author['first_name'] . ' ' . $author['last_name']);
                            }
                        }
                    }
                }
                ?>
                <article class="dataset-card">
                    <div class="dataset-content">
                        <h2>
                            <a href="statii.php?id=<?= $article['id'] ?>">
                                <?= htmlspecialchars($article['title']) ?>
                            </a>
                        </h2>
                        
                        <div class="dataset-meta">
                            <div class="meta-item">
                                <strong>Дата:</strong>
                                <?= date('d.m.Y', strtotime($article['publication_date'])) ?>
                            </div>
                            
                            <?php if ($article['journal_name']): ?>
                                <div class="meta-item">
                                    <strong>Журнал:</strong>
                                    <?= htmlspecialchars($article['journal_name']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($author_names)): ?>
                                <div class="meta-item">
                                    <strong>Авторы:</strong>
                                    <?= implode(', ', array_slice($author_names, 0, 3)) ?>
                                    <?= count($author_names) > 3 ? ' и др.' : '' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <p class="dataset-description">
                            <?= htmlspecialchars(mb_substr($article['abstract'], 0, 200)) ?>
                            <?= mb_strlen($article['abstract']) > 200 ? '...' : '' ?>
                        </p>
                                            
                        <div class="dataset-actions">
                            <a href="statii.php?id=<?= $article['id'] ?>" class="btn">Читать статью</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="page-link">← Предыдущая</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-link current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="page-link">Следующая →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <div class="no-data">
            <p>Научные статьи пока не опубликованы. Следите за обновлениями!</p>
            <a href="index.php" class="btn">На главную</a>
        </div>
    <?php endif; ?>
    
</main>

<?php
} // Конец else для списка статей

require_once 'obschee/footer.php';
?>