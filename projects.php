<?php
$page_title = "Проекты";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Проверяем, запрошен ли конкретный проект
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id > 0) {
    // Показываем конкретный проект
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND status = 'published'");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if ($project) {
        // Убираем или комментируем увеличение счетчика просмотров, если нет колонки views_count
        // $stmt = $pdo->prepare("UPDATE projects SET views_count = views_count + 1 WHERE id = ?");
        // $stmt->execute([$project_id]);
        ?>
        <main class="container">
            
                <h1><?= htmlspecialchars($project['title']) ?></h1>
            
            
            <div class="article-detail">
                <div class="article-header">
                    <span class="dataset-access <?= getStatusClass($project['current_status']) ?>">
                        Статус: <?= getStatusLabel($project['current_status']) ?>
                    </span>
                </div>
                
                <div class="article-meta" style="margin-bottom: 2rem;">
                    <div class="meta-item">
                        <strong>Дата начала:</strong> <?= date('d.m.Y', strtotime($project['start_date'])) ?>
                    </div>
                    <?php if ($project['end_date']): ?>
                        <div class="meta-item">
                            <strong>Дата завершения:</strong> <?= date('d.m.Y', strtotime($project['end_date'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($project['featured_image_url']): ?>
                    <div class="project-image" style="margin: 2rem 0; text-align: center;">
                        <img src="<?= htmlspecialchars($project['featured_image_url']) ?>" 
                             alt="<?= htmlspecialchars($project['title']) ?>"
                             style="max-width: 100%; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                    </div>
                <?php endif; ?>
                
                <div class="article-abstract" style="margin-bottom: 2rem;">
                    <h2>Описание проекта</h2>
                    <div class="abstract-content">
                        <?= nl2br(htmlspecialchars($project['full_description'] ?? $project['short_description'])) ?>
                    </div>
                </div>
                
                <?php if ($project['external_link']): ?>
                    <div class="project-external-link" style="margin: 2rem 0; text-align: center;">
                        <a href="<?= htmlspecialchars($project['external_link']) ?>" class="btn" target="_blank">🌐 Сайт проекта</a>
                    </div>
                <?php endif; ?>
                
                <div class="article-back" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--light-gray);">
                    <a href="projects.php" class="btn btn-secondary">← Назад к списку проектов</a>
                </div>
            </div>
        </main>
        <?php
    } else {
        // Проект не найден
        echo '<main class="container"><div class="no-data"><p>Проект не найден</p><a href="projects.php" class="btn">Вернуться к списку</a></div></main>';
    }
} else {
    // Показываем список проектов
    // Получаем номер страницы из URL
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;

    // Фильтр по статусу
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

    // Формируем WHERE условия и параметры
    $where_conditions = ["status = 'published'"];
    $params = [];

    if ($status_filter !== 'all' && in_array($status_filter, ['active', 'completed', 'planned', 'on_hold'])) {
        $where_conditions[] = "current_status = :status";
        $params[':status'] = $status_filter;
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Получаем общее количество проектов
    $count_sql = "SELECT COUNT(*) as total FROM projects WHERE $where_clause";
    $stmt = $pdo->prepare($count_sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_projects = $stmt->fetch()['total'];
    $total_pages = ceil($total_projects / $per_page);

    // Получаем проекты для текущей страницы
    $sql = "SELECT * FROM projects 
            WHERE $where_clause 
            ORDER BY 
              CASE current_status 
                  WHEN 'active' THEN 1
                  WHEN 'planned' THEN 2
                  WHEN 'on_hold' THEN 3
                  WHEN 'completed' THEN 4
                  ELSE 5
              END,
              start_date DESC 
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $projects = $stmt->fetchAll();

    // Статусы для фильтра
    $statuses = [
        'all' => 'Все проекты',
        'active' => 'Активные',
        'completed' => 'Завершенные',
        'planned' => 'Планируемые',
        'on_hold' => 'Приостановленные'
    ];
?>

<main class="container">
    <!-- Заголовок страницы -->
    <div class="data-header">
        <h1>Научные проекты</h1>
    </div>

    <!-- Фильтр по статусу -->
    <div class="data-filters">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="status">Статус проекта:</label>
                    <select name="status" id="status">
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?= $value ?>" 
                                    <?= $status_filter === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Применить фильтр</button>
                    <a href="projects.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </div>
        </form>
    </div>

    <?php if ($total_projects > 0): ?>
        <div class="datasets-container">
            <div class="datasets-grid">
                <?php foreach ($projects as $project): ?>
                    <article class="dataset-card">
                        <div class="dataset-header">
                            <span class="dataset-access <?= getStatusClass($project['current_status']) ?>">
                                <?= getStatusLabel($project['current_status']) ?>
                            </span>
                            
                            <?php if ($project['featured_image_url']): ?>
                                <span class="dataset-count">
                                    📷 Фото
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="dataset-content">
                            <h2>
                                <a href="projects.php?id=<?= $project['id'] ?>">
                                    <?= htmlspecialchars($project['title']) ?>
                                </a>
                            </h2>
                            
                            <div class="dataset-meta">
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
                            </div>
                            
                            <?php if ($project['short_description']): ?>
                                <p class="dataset-description">
                                    <?= htmlspecialchars(mb_substr($project['short_description'], 0, 120)) ?>
                                    <?= mb_strlen($project['short_description']) > 120 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                                                     
                            <div class="dataset-actions">
                                <a href="projects.php?id=<?= $project['id'] ?>" class="btn">Подробнее</a>
                                <?php if ($project['external_link']): ?>
                                    <a href="<?= htmlspecialchars($project['external_link']) ?>" class="btn btn-secondary" target="_blank">
                                        Сайт
                                    </a>
                                <?php endif; ?>
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
        </div>
        
    <?php else: ?>
        <div class="no-data">
            <p>Проектов по выбранному фильтру не найдено.</p>
            <a href="projects.php" class="btn">Показать все проекты</a>
        </div>
    <?php endif; ?>
</main>

<?php
} // Конец else для списка проектов

// Функции для статусов
function getStatusLabel($status) {
    $labels = [
        'active' => 'Активный',
        'completed' => 'Завершен',
        'planned' => 'Планируется',
        'on_hold' => 'Приостановлен'
    ];
    return $labels[$status] ?? $status;
}

function getStatusClass($status) {
    $classes = [
        'active' => 'active',
        'completed' => 'completed',
        'planned' => 'planned',
        'on_hold' => 'on_hold'
    ];
    return $classes[$status] ?? '';
}

require_once 'obschee/footer.php';
?>