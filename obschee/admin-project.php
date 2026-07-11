<?php 
session_start();
require_once 'connect.php';  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['login'])) {
    header('Location: admin-login.php');
    exit();
}

    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

// Создание/редактирование проекта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    try {
        $title = trim($_POST['title'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $current_status = $_POST['current_status'] ?? 'planned';
        $funding_source = trim($_POST['funding_source'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        
        // Валидация обязательных полей
        if (empty($title) || empty($short_description) || empty($full_description)) {
            throw new Exception('Заполните обязательные поля');
        }
        
        // Подготовка данных в JSON формате
        $objectives = trim($_POST['objectives'] ?? '');
        $objectives_array = array_filter(array_map('trim', explode("\n", $objectives)));
        $objectives_json = json_encode($objectives_array, JSON_UNESCAPED_UNICODE);
        
        $team_members = trim($_POST['team_members'] ?? '');
        $team_members_array = array_filter(array_map('trim', explode(',', $team_members)));
        $team_members_json = json_encode($team_members_array, JSON_UNESCAPED_UNICODE);
        
        // Генерация slug
        $slug = generateSlug($title);
        
        if ($id > 0) {
            // Редактирование существующего проекта
            $sql = "UPDATE projects SET 
                    title = ?, slug = ?, short_description = ?, full_description = ?,
                    objectives = ?, start_date = ?, end_date = ?, current_status = ?,
                    funding_source = ?, budget = ?, team_members = ?, status = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title, $slug, $short_description, $full_description,
                $objectives_json, $start_date, $end_date, $current_status,
                $funding_source, $budget, $team_members_json, $status, $id
            ]);
            
            $message = 'Проект обновлен успешно!';
        } else {
            // Создание нового проекта
            $sql = "INSERT INTO projects 
                    (title, slug, short_description, full_description, objectives, 
                     start_date, end_date, current_status, funding_source, budget, 
                     team_members, status, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title, $slug, $short_description, $full_description, $objectives_json,
                $start_date, $end_date, $current_status, $funding_source, $budget,
                $team_members_json, $status, $_SESSION['user_id'] ?? 1
            ]);
            
            $message = 'Проект создан успешно!';
            $id = $pdo->lastInsertId(); // Получаем ID нового проекта
        }
        
        // // Редирект после успешного сохранения
        header('Location: admin-project.php');
        exit();
        
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}
// Удаление проекта
if ($action === 'delete' && $id > 0) {
    $sql = "DELETE FROM projects WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    $message = 'Проект удален успешно!';
    $action = 'list';
}

// Получение данных проекта для редактирования
$project_data = [];
if (($action === 'edit' || $action === 'save') && $id > 0) {
    $sql = "SELECT * FROM projects WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $project_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Декодирование JSON полей
    if ($project_data) {
        $project_data['objectives_array'] = json_decode($project_data['objectives'] ?? '[]', true) ?: [];
        $project_data['objectives_text'] = implode("\n", $project_data['objectives_array']);
        $project_data['team_members_array'] = json_decode($project_data['team_members'] ?? '[]', true) ?: [];
        $project_data['team_members_text'] = implode(', ', $project_data['team_members_array']);
    }
}

// Получение списка проектов
if ($action === 'list') {
    $sql = "SELECT p.*, u.first_name, u.last_name 
            FROM projects p 
            LEFT JOIN users u ON p.created_by = u.id 
            ORDER BY p.created_at DESC 
            LIMIT 50";
    $projects = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Получение статистики
    $sql_stats = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN current_status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN current_status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN current_status = 'planned' THEN 1 ELSE 0 END) as planned
        FROM projects";
    $stats = $pdo->query($sql_stats)->fetch(PDO::FETCH_ASSOC);
}

// Получение списка пользователей для команды
$sql_users = "SELECT id, first_name, last_name, position FROM users WHERE is_active = 1 ORDER BY first_name";
$users = $pdo->query($sql_users)->fetchAll(PDO::FETCH_ASSOC);

// Функция генерации slug
function generateSlug($title) {
    $slug = mb_strtolower($title, 'UTF-8');
    $slug = preg_replace('/[^a-z0-9а-яё\s-]/u', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Проекты</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="page-header">
            <a href="admin-project.php">
            <h1><i class="fas fa-project-diagram"></i> Управление проектами</h1></a>
            <p>Создание и редактирование научных проектов</p>
             <!-- <p>Количество статей: <php echo count($projects); ?></p> -->
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
        <!-- Список проектов -->
        <!-- <div class="card mb-2"> -->
            <!-- <div class="stats-grid">
                <div class="stat-card"> -->
                    
                <!-- </div>
            </div> -->
        <!-- </div> -->
        
        <div class="card mb-2">
            <div class="action-buttons">
                <a href="?action=new" class="btn btn-success">
                    <i class="fas fa-plus"></i> Новый проект
                </a>
                <!-- <div class="btn-group">
                    <a href="?" class="btn btn-secondary">Все</a>
                    <a href="?status=active" class="btn btn-secondary">Активные</a>
                    <a href="?status=completed" class="btn btn-secondary">Завершенные</a>
                </div> -->
            </div>
        </div>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Статус</th>
                        <th>Сроки</th>
                        <th>Бюджет</th>
                        <th>Финансирование</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo $project['id']; ?></td>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td>
                            <span class="status-badge 
                                <?php 
                                switch($project['current_status']) {
                                    case 'active': echo 'status-published'; break;
                                    case 'completed': echo 'status-completed'; break;
                                    case 'planned': echo 'status-draft'; break;
                                    default: echo 'status-draft';
                                }
                                ?>">
                                <?php 
                                $status_text = [
                                    'active' => 'Активный',
                                    'completed' => 'Завершен',
                                    'planned' => 'Запланирован',
                                    'on_hold' => 'Приостановлен'
                                ];
                                echo $status_text[$project['current_status']] ?? $project['current_status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('d.m.Y', strtotime($project['start_date'])); ?>
                            <?php if ($project['end_date']): ?>
                            - <?php echo date('d.m.Y', strtotime($project['end_date'])); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($project['budget'] > 0): ?>
                            <?php echo number_format($project['budget'], 0, ',', ' '); ?> ₽
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($project['funding_source'] ?? ''); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($project['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $project['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Удалить этот проект?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="../projects.php?slug=<?php echo $project['slug']; ?>" target="_blank" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php elseif ($action === 'new' || $action === 'edit'): ?>
        <!-- Форма создания/редактирования проекта -->
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> 
                <?php echo $action === 'new' ? 'Новый проект' : 'Редактирование проекта'; ?>
            </h1>
        </div>
        
        <div class="card">
            <form method="POST" action="?action=save<?php echo $id > 0 ? '&id=' . $id : ''; ?>">
                <div class="form-group">
                    <label for="title">Название проекта *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($project_data['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="short_description">Краткое описание *</label>
                    <textarea id="short_description" name="short_description" rows="3" required><?php echo htmlspecialchars($project_data['short_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="full_description">Полное описание *</label>
                    <textarea id="full_description" name="full_description" rows="6" required><?php echo htmlspecialchars($project_data['full_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="objectives">Цели и задачи (каждая с новой строки) *</label>
                    <textarea id="objectives" name="objectives" rows="4" required><?php echo htmlspecialchars($project_data['objectives_text'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Дата начала *</label>
                        <input type="date" id="start_date" name="start_date" required 
                               value="<?php echo $project_data['start_date'] ?? date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">Дата окончания</label>
                        <input type="date" id="end_date" name="end_date" 
                               value="<?php echo $project_data['end_date'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_status">Текущий статус *</label>
                        <select id="current_status" name="current_status" required>
                            <option value="planned" <?php echo ($project_data['current_status'] ?? 'planned') === 'planned' ? 'selected' : ''; ?>>Запланирован</option>
                            <option value="active" <?php echo ($project_data['current_status'] ?? '') === 'active' ? 'selected' : ''; ?>>Активный</option>
                            <option value="completed" <?php echo ($project_data['current_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Завершен</option>
                            <option value="on_hold" <?php echo ($project_data['current_status'] ?? '') === 'on_hold' ? 'selected' : ''; ?>>Приостановлен</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Статус публикации</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($project_data['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="published" <?php echo ($project_data['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="funding_source">Источник финансирования</label>
                        <input type="text" id="funding_source" name="funding_source" 
                               value="<?php echo htmlspecialchars($project_data['funding_source'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="budget">Бюджет (₽)</label>
                        <input type="number" id="budget" name="budget" step="0.01" 
                               value="<?php echo $project_data['budget'] ?? 0; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="team_members">Участники проекта (через запятую)</label>
                    <input type="text" id="team_members" name="team_members" 
                           value="<?php echo htmlspecialchars($project_data['team_members_text'] ?? ''); ?>">
                    <small class="text-muted">Можно вводить имена или email участников</small>
                </div>
                
                <div class="form-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Сохранить проект
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>