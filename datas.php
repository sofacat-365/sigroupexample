<?php
$page_title = "Наборы данных";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем номер страницы из URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Фильтры
$access_level = $_GET['access'] ?? 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Построение запроса
$where_conditions = ["d.access_level IN ('public', 'restricted')"];
$params = [];

if ($access_level !== 'all' && in_array($access_level, ['public', 'restricted'])) {
    $where_conditions[] = "d.access_level = :access_level";
    $params[':access_level'] = $access_level;
}

if ($search_query) {
    $where_conditions[] = "(d.title LIKE :search OR d.description LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Получаем общее количество
$count_sql = "SELECT COUNT(*) as total FROM datasets d $where_sql";
$stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_datasets = $stmt->fetch()['total'];
$total_pages = ceil($total_datasets / $per_page);

// Получаем данные для текущей страницы
$sql = "SELECT d.*, 
               u.first_name as author_first_name,
               u.last_name as author_last_name
        FROM datasets d
        LEFT JOIN users u ON d.created_by = u.id
        $where_sql
        ORDER BY d.created_at DESC
        LIMIT :limit OFFSET :offset";

$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$datasets = $stmt->fetchAll();

$access_types = [
    'all' => 'Все уровни доступа',
    'public' => 'Публичные',
    'restricted' => 'Ограниченные'
];
?>

<main class="container">
    <div class="data-header">
        <h1>Научные данные</h1>
    </div>

    <div class="page-description">
        <p>На этой странице мы публикуем научные данные, полученные в ходе наших исследований.</p>
    </div>

    <!-- Поиск и фильтры -->
    <div class="data-filters">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <input type="text" 
                       name="search" 
                       placeholder="Поиск по названию или описанию..." 
                       value="<?= htmlspecialchars($search_query) ?>"
                       class="search-input">
                <button type="submit" class="search-btn">🔍</button>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="access_level">Уровень доступа:</label>
                    <select name="access" id="access_level">
                        <?php foreach ($access_types as $value => $label): ?>
                            <option value="<?= $value ?>" 
                                    <?= $access_level === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Применить фильтры</button>
                    <a href="datas.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Список данных -->
    <div class="datasets-container">
        <?php if ($total_datasets > 0): ?>
            <div class="datasets-grid">
                <?php foreach ($datasets as $dataset): 
                    // Парсим preview_data для карточки
                    $preview_text = $dataset['preview_data'];
                    $preview_info = [];
                    
                    if ($preview_text) {
                        // Ищем JSON в тексте
                        if (preg_match('/\{.*\}/s', $preview_text, $matches)) {
                            $json_str = $matches[0];
                            $preview_info = json_decode($json_str, true);
                        }
                    }
                ?>
                    <article class="dataset-card">
                        <div class="dataset-header">
                            <span class="dataset-access <?= htmlspecialchars($dataset['access_level']) ?>">
                                <?= $dataset['access_level'] === 'public' ? 'Публичный' : 'Ограниченный' ?>
                            </span>
                            
                            <?php if (isset($preview_info['total_documents'])): ?>
                                <span class="dataset-count">
                                    📊 <?= number_format($preview_info['total_documents'], 0, '', ' ') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="dataset-content">
                            <h2>
                                <a href="data.php?id=<?= $dataset['id'] ?>">
                                    <?= htmlspecialchars($dataset['title']) ?>
                                </a>
                            </h2>
                            
                            <div class="dataset-meta">
                                <div class="meta-item">
                                    <strong>Создан:</strong>
                                    <?= date('d.m.Y', strtotime($dataset['created_at'])) ?>
                                </div>
                                
                                <?php if ($dataset['author_first_name'] || $dataset['author_last_name']): ?>
                                    <div class="meta-item">
                                        <strong>Автор:</strong>
                                        <?= htmlspecialchars(trim($dataset['author_first_name'] . ' ' . $dataset['author_last_name'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Краткая информация из preview -->
                            <?php if (!empty($preview_info)): ?>
                                <div class="dataset-preview-info">
                                    <?php if (isset($preview_info['languages']) && is_array($preview_info['languages'])): ?>
                                        <div class="preview-languages">
                                            <?php foreach ($preview_info['languages'] as $lang): ?>
                                                <span class="lang-badge"><?= htmlspecialchars($lang) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($preview_info['fields'])): 
                                        $field_count = count($preview_info['fields']); ?>
                                        <div class="preview-fields-count">
                                            <small>Поля: <?= $field_count ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <p class="dataset-description">
                                <?= htmlspecialchars(mb_substr($dataset['description'], 0, 120)) ?>
                                <?= mb_strlen($dataset['description']) > 120 ? '...' : '' ?>
                            </p>
                            
                            <div class="dataset-actions">
                                <a href="data.php?id=<?= $dataset['id'] ?>" class="btn">
                                    <?php if ($dataset['access_level'] === 'restricted'): ?>
                                        Запросить доступ
                                    <?php else: ?>
                                        Подробнее
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="prev">← Назад</a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Страница <?= $page ?> из <?= $total_pages ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           class="next">Вперед →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-data">
                <p>Наборы данных не найдены.</p>
                <?php if ($search_query || $access_level !== 'all'): ?>
                    <a href="datas.php" class="btn">Показать все данные</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>