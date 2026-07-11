<?php
$page_title = "Блог и новости";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Поиск по тексту
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_conditions = ["bp.status = 'published'"];
$params = [];

if ($search_query) {
    $where_conditions[] = "(bp.title LIKE :search OR bp.excerpt LIKE :search OR bp.content LIKE :search OR bp.tags LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$count_sql = "SELECT COUNT(*) as total FROM blog_posts bp $where_sql";
$stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_posts = $stmt->fetch()['total'];
$total_pages = ceil($total_posts / $per_page);

$sql = "
    SELECT bp.*, u.first_name, u.last_name
    FROM blog_posts bp
    LEFT JOIN users u ON bp.author_id = u.id
    $where_sql
    ORDER BY bp.published_at DESC 
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

// Привязываем параметры поиска
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

// Привязываем параметры пагинации
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$posts = $stmt->fetchAll();

?>

<main class="container">
    <!-- Заголовок страницы -->
    <div class="data-header">
        <h1>Блог и новости</h1>
    </div>

    <!-- Поиск по тексту (стилизован как на странице данных) -->
    <div class="data-filters">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <input type="text" 
                       name="search" 
                       id="search_input" 
                       placeholder="Поиск по тексту..." 
                       value="<?= htmlspecialchars($search_query) ?>"
                       class="search-input">
                <button type="submit" class="search-btn">🔍 Найти</button>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <!-- Пустой div для выравнивания -->
                </div>
                
                <div class="filter-actions">
                    <?php if ($search_query): ?>
                        <a href="blogs.php" class="btn btn-secondary">Сбросить поиск</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Отображение поискового запроса -->
    <?php if ($search_query): ?>
    <div class="post-tags" style="margin-bottom: 1.5rem;">
        <span class="tag" style="background: var(--primary-light, #e8f4f8); color: var(--primary-color);">
            Поиск: "<?= htmlspecialchars($search_query) ?>"
            <a href="blogs.php" style="margin-left: 0.5rem; color: inherit; text-decoration: none;">✕</a>
        </span>
    </div>
    <?php endif; ?>

    <!-- Список записей блога -->
    <div class="datasets-container">
        <?php if ($total_posts > 0): ?>
            <div class="datasets-grid">
                <?php foreach ($posts as $post): ?>
                <article class="dataset-card">
                    <div class="dataset-header">
                                       
                        <?php if (isset($post['views_count']) && $post['views_count'] > 0): ?>
                            <span class="dataset-count">
                                👁 <?= number_format($post['views_count'], 0, '', ' ') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dataset-content">
                        <h2>
                            <a href="blog.php?id=<?= $post['id'] ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h2>
                        
                        <div class="dataset-meta">
                            <div class="meta-item">
                                <strong>Опубликовано:</strong>
                                <?= $post['published_at'] ? date('d.m.Y', strtotime($post['published_at'])) : 'Дата не указана' ?>
                            </div>
                            
                            <?php if ($post['first_name']): ?>
                                <div class="meta-item">
                                    <strong>Автор:</strong>
                                    <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                        $tags = json_decode($post['tags'] ?? '[]', true);
                        if (is_array($tags) && !empty($tags)): 
                        ?>
                            <div class="post-tags" style="margin: 1rem 0;">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($post['excerpt']): ?>
                            <p class="dataset-description">
                                <?= htmlspecialchars(mb_substr($post['excerpt'], 0, 120)) ?>
                                <?= mb_strlen($post['excerpt']) > 120 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="dataset-actions">
                            <a href="blog.php?id=<?= $post['id'] ?>" class="btn">
                                Читать
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Пагинация (стилизована как на странице данных) -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="prev">← Назад</a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Страница <?= $page ?> из <?= $total_pages ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="next">Вперед →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
            <div class="no-data">
                <p>
                    <?php if ($search_query): ?>
                        По запросу "<?= htmlspecialchars($search_query) ?>" ничего не найдено.
                    <?php else: ?>
                        Записей в блоге пока нет. Зайдите позже.
                    <?php endif; ?>
                </p>
                <?php if ($search_query): ?>
                    <a href="blogs.php" class="btn">Показать все записи</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>