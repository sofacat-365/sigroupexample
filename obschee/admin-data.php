<?php
// admin/admin-data.php
session_start();  // Всегда в начале!
require_once 'connect.php';  

if (!isset($_SESSION['login'])) {
    header('Location: admin-login.php');  // Если не залогинен - обратно на вход
    exit();
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Обработка удаления через GET параметр
if ($action === 'delete' && $id > 0) {
    try {
        // Проверяем, существует ли запись
        $checkStmt = $pdo->prepare("SELECT id, title FROM datasets WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        $dataset = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dataset) {
            // Удаляем запись
            $stmt = $pdo->prepare("DELETE FROM datasets WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $_SESSION['message'] = 'Набор данных "' . htmlspecialchars($dataset['title']) . '" успешно удален';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Набор данных не найден';
            $_SESSION['message_type'] = 'error';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Ошибка удаления: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: admin-data.php');
    exit();
}

// Обработка POST запросов (создание/редактирование)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Добавление/редактирование набора данных
    if (isset($_POST['save_dataset'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $access_level = $_POST['access_level'];
        $related_project_id = !empty($_POST['related_project_id']) ? (int)$_POST['related_project_id'] : null;
        
        if (empty($title) || empty($description)) {
            $error = 'Заполните обязательные поля';
        } else {
            try {
                if ($id > 0) {
                    // Редактирование существующего набора данных
                    $stmt = $pdo->prepare("
                        UPDATE datasets 
                        SET title = :title, 
                            description = :description,
                            access_level = :access_level,
                            related_project_id = :related_project_id,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    
                    $stmt->execute([
                        ':title' => $title,
                        ':description' => $description,
                        ':access_level' => $access_level,
                        ':related_project_id' => $related_project_id,
                        ':id' => $id
                    ]);
                    
                    $_SESSION['message'] = 'Набор данных успешно обновлен';
                    $_SESSION['message_type'] = 'success';
                } else {
                    // Добавление нового набора данных
                    $userId = $_SESSION['admin_user_id'] ?? 1;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO datasets 
                        (title, description, access_level, 
                         related_project_id, created_by)
                        VALUES (:title, :description, :access_level,
                                :related_project_id, :created_by)
                    ");
                    
                    $stmt->execute([
                        ':title' => $title,
                        ':description' => $description,
                        ':access_level' => $access_level,
                        ':related_project_id' => $related_project_id,
                        ':created_by' => $userId
                    ]);
                    
                    $_SESSION['message'] = 'Набор данных успешно добавлен';
                    $_SESSION['message_type'] = 'success';
                }
                
                header('Location: admin-data.php');
                exit();
                
            } catch (PDOException $e) {
                $error = 'Ошибка сохранения: ' . $e->getMessage();
            }
        }
    }
}

// Получение сообщений из сессии
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Получение данных для отображения
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM datasets WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $dataset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$dataset) {
            $error = 'Набор данных не найден';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка получения данных: ' . $e->getMessage();
        $action = 'list';
    }
}

// Получение списка проектов для выпадающего списка
try {
    $projectsStmt = $pdo->query("SELECT id, title FROM projects WHERE status = 'published' ORDER BY title");
    $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Данные</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="page-header">
            <h1><i class="fas fa-database"></i> Управление данными</h1>
            <p>Загрузка и управление результатами экспериментов</p>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
        <!-- Список наборов данных -->
        <div class="card mb-2">
            <div class="action-buttons">
                <a href="?action=new" class="btn btn-success">
                    <i class="fas fa-plus"></i> Добавить набор данных
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Проект</th>
                            <th>Доступ</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Получаем список наборов данных с информацией о проектах
                            $stmt = $pdo->prepare("
                                SELECT d.*, p.title as project_title 
                                FROM datasets d 
                                LEFT JOIN projects p ON d.related_project_id = p.id 
                                ORDER BY d.created_at DESC
                                LIMIT 50
                            ");
                            $stmt->execute();
                            $datasets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($datasets)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Наборы данных не найдены
                                        </div>
                                    </td>
                                </tr>
                            <?php else:
                                foreach ($datasets as $dataset): 
                                    // Определяем стиль для статуса доступа
                                    $accessClass = '';
                                    $accessLabel = '';
                                    switch ($dataset['access_level']) {
                                        case 'public':
                                            $accessClass = 'status-published';
                                            $accessLabel = 'Открытый';
                                            break;
                                        case 'restricted':
                                            $accessClass = 'status-warning';
                                            $accessLabel = 'Ограниченный';
                                            break;
                                        case 'private':
                                            $accessClass = 'status-danger';
                                            $accessLabel = 'Приватный';
                                            break;
                                        default:
                                            $accessClass = 'status-draft';
                                            $accessLabel = $dataset['access_level'];
                                    }
                                    
                                    // Форматируем дату
                                    $date = date('d.m.Y', strtotime($dataset['created_at']));
                            ?>
                            <tr>
                                <td><?php echo $dataset['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($dataset['title']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($dataset['description'], 0, 100)) . '...'; ?></small>
                                </td>
                                <td>
                                    <?php if ($dataset['project_title']): ?>
                                        <?php echo htmlspecialchars($dataset['project_title']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $accessClass; ?>">
                                        <?php echo $accessLabel; ?>
                                    </span>
                                </td>
                                <td><?php echo $date; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=edit&id=<?php echo $dataset['id']; ?>" class="btn btn-primary btn-sm" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=view&id=<?php echo $dataset['id']; ?>" class="btn btn-secondary btn-sm" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="confirmDelete(<?php echo $dataset['id']; ?>, '<?php echo htmlspecialchars(addslashes($dataset['title'])); ?>')"
                                                title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach;
                            endif;
                        } catch (PDOException $e) { ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Ошибка загрузки данных: <?php echo htmlspecialchars($e->getMessage()); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php elseif ($action === 'new' || $action === 'edit'): ?>
        <!-- Форма создания/редактирования набора данных -->
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> 
                <?php echo $id > 0 ? 'Редактирование набора данных' : 'Новый набор данных'; ?>
            </h1>
        </div>
        
        <div class="card">
            <form method="POST" action="">
                <?php if ($id > 0): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Название набора данных *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo isset($dataset['title']) ? htmlspecialchars($dataset['title']) : ''; ?>"
                           placeholder="Например: Спектры РФЭС наночастиц Pt/CeO₂">
                </div>
                
                <div class="form-group">
                    <label for="description">Описание набора данных *</label>
                    <textarea id="description" name="description" rows="4" required 
                              placeholder="Опишите методы получения данных, условия эксперимента, единицы измерения..."><?php echo isset($dataset['description']) ? htmlspecialchars($dataset['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="access_level">Уровень доступа *</label>
                        <select id="access_level" name="access_level" required>
                            <option value="public" <?php echo (isset($dataset['access_level']) && $dataset['access_level'] === 'public') ? 'selected' : ''; ?>>Открытый доступ</option>
                            <option value="restricted" <?php echo (isset($dataset['access_level']) && $dataset['access_level'] === 'restricted') ? 'selected' : ''; ?>>Ограниченный доступ</option>
                            <option value="private" <?php echo (isset($dataset['access_level']) && $dataset['access_level'] === 'private') ? 'selected' : ''; ?>>Приватный доступ</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="related_project_id">Связанный проект</label>
                        <select id="related_project_id" name="related_project_id">
                            <option value="">Не привязан к проекту</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"
                                <?php echo (isset($dataset['related_project_id']) && $dataset['related_project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" name="save_dataset" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $id > 0 ? 'Обновить' : 'Сохранить'; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <?php elseif ($action === 'view' && $id > 0): ?>
        <!-- Просмотр деталей набора данных -->
        <?php
        try {
            $stmt = $pdo->prepare("
                SELECT d.*, p.title as project_title, u.username as creator 
                FROM datasets d 
                LEFT JOIN projects p ON d.related_project_id = p.id 
                LEFT JOIN users u ON d.created_by = u.id 
                WHERE d.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $dataset = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$dataset) {
                throw new Exception('Набор данных не найден');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $action = 'list';
        }
        
        if ($dataset):
        ?>
        <div class="page-header">
            <h1><i class="fas fa-eye"></i> Просмотр набора данных</h1>
        </div>
        
        <div class="card">
            <div class="dataset-header">
                <h2><?php echo htmlspecialchars($dataset['title']); ?></h2>
                <div class="dataset-meta">
                    <span class="status-badge <?php 
                        echo $dataset['access_level'] === 'public' ? 'status-published' : 
                             ($dataset['access_level'] === 'restricted' ? 'status-warning' : 'status-danger'); 
                    ?>">
                        <?php 
                        $accessLabels = [
                            'public' => 'Открытый доступ',
                            'restricted' => 'Ограниченный доступ',
                            'private' => 'Приватный доступ'
                        ];
                        echo $accessLabels[$dataset['access_level']] ?? $dataset['access_level'];
                        ?>
                    </span>
                    <span><i class="fas fa-calendar"></i> Создан: <?php echo date('d.m.Y H:i', strtotime($dataset['created_at'])); ?></span>
                    <?php if ($dataset['updated_at'] !== $dataset['created_at']): ?>
                    <span><i class="fas fa-edit"></i> Обновлен: <?php echo date('d.m.Y H:i', strtotime($dataset['updated_at'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dataset-content">
                <h3>Описание</h3>
                <p><?php echo nl2br(htmlspecialchars($dataset['description'])); ?></p>
                
                <div class="dataset-details">
                    <?php if ($dataset['project_title']): ?>
                    <div class="detail-item">
                        <strong>Проект:</strong> <?php echo htmlspecialchars($dataset['project_title']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($dataset['creator']): ?>
                    <div class="detail-item">
                        <strong>Создатель:</strong> <?php echo htmlspecialchars($dataset['creator']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="?action=edit&id=<?php echo $dataset['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <button type="button" class="btn btn-danger" 
                        onclick="confirmDelete(<?php echo $dataset['id']; ?>, '<?php echo htmlspecialchars(addslashes($dataset['title'])); ?>')">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </main>
    
    <!-- Модальное окно подтверждения удаления -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Подтверждение удаления</h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить набор данных "<span id="deleteTitle"></span>"?</p>
                <p class="text-danger"><strong>Внимание:</strong> Это действие нельзя отменить! Все связанные файлы также будут удалены.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Отмена</button>
                <a href="#" id="deleteLink" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, title) {
            document.getElementById('deleteTitle').textContent = title;
            document.getElementById('deleteLink').href = '?action=delete&id=' + id;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
        
        // Обработка клавиши Escape для закрытия модального окна
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>