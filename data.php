<?php
$page_title = "Научные данные";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем ID набора данных
$dataset_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($dataset_id <= 0) {
    header('Location: datas.php');
    exit;
}

// Получаем набор данных
$stmt = $pdo->prepare("SELECT d.*, 
                       p.title as project_title,
                       u.first_name as author_first_name,
                       u.last_name as author_last_name
                       FROM datasets d
                       LEFT JOIN projects p ON d.related_project_id = p.id
                       LEFT JOIN users u ON d.created_by = u.id
                       WHERE d.id = ? AND d.access_level IN ('public', 'restricted')");
$stmt->execute([$dataset_id]);
$dataset = $stmt->fetch();

if (!$dataset) {
    header('Location: datas.php');
    exit;
}

// Парсим preview_data если есть
$preview_data = [];
$preview_text = $dataset['preview_data'];
$parsed_from_json = false;

if ($preview_text) {
    // Пытаемся найти JSON в тексте
    if (preg_match('/\{.*\}/s', $preview_text, $matches)) {
        $json_str = $matches[0];
        $preview_data = json_decode($json_str, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $parsed_from_json = true;
        }
    }
    
    // Если не удалось распарсить как JSON, оставляем как текст
    if (!$parsed_from_json) {
        $preview_data = ['raw_text' => $preview_text];
    }
}

// Форматируем метаданные
$access_labels = [
    'public' => 'Публичный',
    'restricted' => 'Ограниченный',
    'private' => 'Приватный'
];
$access_label = $access_labels[$dataset['access_level']] ?? $dataset['access_level'];
?>

<main class="container">
    <nav class="breadcrumbs">
        <a href="datas.php">Наборы данных</a> &gt; 
        <span><?= htmlspecialchars($dataset['title']) ?></span>
    </nav>

    <div class="article-detail">
        <!-- Заголовок и мета-информация -->
        <div class="article-header">
            <h1><?= htmlspecialchars($dataset['title']) ?></h1>
            
            <div class="article-meta">
                <div class="meta-item">
                    <strong>Уровень доступа:</strong>
                    <span class="dataset-access <?= htmlspecialchars($dataset['access_level']) ?>">
                        <?= $access_label ?>
                    </span>
                </div>
                
                <div class="meta-item">
                    <strong>Дата создания:</strong>
                    <?= date('d.m.Y H:i', strtotime($dataset['created_at'])) ?>
                </div>
                
                <div class="meta-item">
                    <strong>Дата обновления:</strong>
                    <?= date('d.m.Y H:i', strtotime($dataset['updated_at'])) ?>
                </div>
                
                <?php if ($dataset['author_first_name'] || $dataset['author_last_name']): ?>
                    <div class="meta-item">
                        <strong>Автор:</strong>
                        <?= htmlspecialchars(trim($dataset['author_first_name'] . ' ' . $dataset['author_last_name'])) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($dataset['project_title']): ?>
                    <div class="meta-item">
                        <strong>Связанный проект:</strong>
                        <a href="projects.php?id=<?= $dataset['related_project_id'] ?>">
                            <?= htmlspecialchars($dataset['project_title']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Предпросмотр данных -->
        <?php if (!empty($preview_data) && $parsed_from_json): ?>
            <div class="article-abstract">
                <h2>Предпросмотр данных</h2>
                <div class="preview-container">
                    <?php if (isset($preview_data['fields'])): ?>
                        <div class="preview-fields">
                            <h3>Поля данных:</h3>
                            <ul>
                                <?php foreach ($preview_data['fields'] as $field): 
                                    if (is_array($field)):
                                        foreach ($field as $key => $value): ?>
                                            <li><strong><?= htmlspecialchars($key) ?>:</strong> 
                                                <?php if (is_array($value)): ?>
                                                    <?= htmlspecialchars(implode(', ', $value)) ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($value) ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach;
                                    else: ?>
                                        <li><?= htmlspecialchars($field) ?></li>
                                    <?php endif;
                                endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($preview_data['sample']) && is_array($preview_data['sample'])): ?>
                        <div class="preview-sample">
                            <h3>Пример записи:</h3>
                            <div class="sample-item">
                                <?php foreach ($preview_data['sample'] as $sample): ?>
                                    <?php if (is_array($sample)): ?>
                                        <div class="sample-card">
                                            <?php foreach ($sample as $key => $value): ?>
                                                <div class="sample-field">
                                                    <strong><?= htmlspecialchars($key) ?>:</strong> 
                                                    <?= htmlspecialchars($value) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p><?= htmlspecialchars($sample) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($preview_data['languages']) && is_array($preview_data['languages'])): ?>
                        <div class="preview-languages">
                            <h3>Языки данных:</h3>
                            <div class="language-tags">
                                <?php foreach ($preview_data['languages'] as $lang): ?>
                                    <span class="language-tag"><?= htmlspecialchars($lang) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($preview_data['total_documents'])): ?>
                        <div class="preview-stats">
                            <h3>Общая статистика:</h3>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <span class="stat-number"><?= number_format($preview_data['total_documents'], 0, '', ' ') ?></span>
                                    <span class="stat-label">Всего документов</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Описание -->
        <div class="article-abstract">
            <h2>Описание набора данных</h2>
            <div class="abstract-content">
                <?= nl2br(htmlspecialchars($dataset['description'])) ?>
            </div>
        </div>
        
        <!-- Информация о доступе -->
        <div class="article-abstract">
            <h2>Информация о доступе</h2>
            <div class="access-info-content">
                <?php if ($dataset['access_level'] === 'public'): ?>
                    <div>
                        <strong>Публичный доступ:</strong> Этот набор данных доступен для свободного использования.
                        Вы можете скачать данные или использовать их в своих исследованиях.
                    </div>
                <?php elseif ($dataset['access_level'] === 'restricted'): ?>
                    <div>
                        <strong>Ограниченный доступ:</strong> Для получения доступа к этому набору данных 
                        необходимо связаться с автором или администрацией проекта.
                    </div>
                    <?php if ($dataset['author_first_name'] || $dataset['author_last_name']): ?>
                        <p><strong>Контактное лицо:</strong> 
                        <?= htmlspecialchars(trim($dataset['author_first_name'] . ' ' . $dataset['author_last_name'])) ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Методология и использование -->
        <div class="article-abstract">
            <h2>Использование данных</h2>
            <div class="usage-content">
                <p>При использовании этого набора данных в публикациях или исследованиях, пожалуйста:</p>
                <ol>
                    <li>Ссылайтесь на оригинальный источник данных</li>
                    <li>Указывайте автора(ов) набора данных</li>
                    <li>Приводите ссылку на эту страницу</li>
                    <li>Сообщайте об ошибках или неточностях автору</li>
                </ol>
            </div>
        </div>
        
        <!-- Действия -->
        <div class="article-back">
            <a href="datas.php" class="btn btn-secondary">
                ← К списку данных
            </a>
            
            <?php if ($dataset['access_level'] === 'public' && $dataset['download_url']): ?>
                <a href="<?= htmlspecialchars($dataset['download_url']) ?>" class="btn" download>
                    Скачать данные
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>