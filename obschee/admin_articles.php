<?php
// admin/admin_articles.php
session_start(); 
require_once 'connect.php';  
if (!isset($_SESSION['login'])) {
    header('Location: admin-login.php');  // прогоняем без логина
    exit();
}
    // Получаем пользователя
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, role FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем всех пользователей для выбора авторов
    $usersStmt = $pdo->query("SELECT id, first_name, last_name, position FROM users WHERE is_active = 1 ORDER BY last_name");
    $allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $action = $_GET['action'] ?? 'list';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $message = '';
    
    // Обработка действий
    switch ($action) {
        case 'new':
        case 'edit':
            // Получение данных статьи для редактирования
            $article = null;
            if ($action === 'edit' && $id > 0) {
                $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $article = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$article) {
                    header('Location: admin_articles.php');
                    exit();
                }
            }
            
            // Сохранение статьи
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $abstract = trim($_POST['abstract'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $authors = isset($_POST['authors']) ? json_encode($_POST['authors']) : '[]';
                $publication_date = $_POST['publication_date'] ?? date('Y-m-d');
                $journal_name = trim($_POST['journal_name'] ?? '');
                $volume = trim($_POST['volume'] ?? '');
                $pages = trim($_POST['pages'] ?? '');
                $status = $_POST['status'] ?? 'draft';
                
                // Валидация
                if (empty($title) || empty($abstract) || empty($content)) {
                    $message = 'Заполните все обязательные поля';
                } else {
                    if ($action === 'new') {
                        // Создание новой статьи
                        $stmt = $pdo->prepare("
                            INSERT INTO articles (
                                title, slug, abstract, content, authors, 
                                publication_date, journal_name, volume, pages,
                                status, created_by
                            ) VALUES (
                                :title, :slug, :abstract, :content, :authors,
                                :publication_date, :journal_name, :volume, :pages,
                                :status, :created_by
                            )
                        ");
                        
                        $result = $stmt->execute([
                            ':title' => $title,
                            ':slug' => $slug ?: strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)),
                            ':abstract' => $abstract,
                            ':content' => $content,
                            ':authors' => $authors,
                            ':publication_date' => $publication_date,
                            ':journal_name' => $journal_name,
                            ':volume' => $volume,
                            ':pages' => $pages,
                            ':status' => $status,
                            ':created_by' => $_SESSION['admin_id']
                        ]);
                        
                        if ($result) {
                            $id = $pdo->lastInsertId();
                            
                            // Логируем действие
                            $logStmt = $pdo->prepare("
                                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details)
                                VALUES (:user_id, 'create', 'article', :entity_id, :details)
                            ");
                            $logStmt->execute([
                                ':user_id' => $_SESSION['admin_id'],
                                ':entity_id' => $id,
                                ':details' => json_encode(['title' => $title])
                            ]);
                            
                            header('Location: admin_articles.php?action=edit&id=' . $id . '&message=created');
                            exit();
                        }
                    } else {
                        // Обновление существующей статьи
                        $stmt = $pdo->prepare("
                            UPDATE articles SET
                                title = :title,
                                slug = :slug,
                                abstract = :abstract,
                                content = :content,
                                authors = :authors,
                                publication_date = :publication_date,
                                journal_name = :journal_name,
                                volume = :volume,
                                pages = :pages,
                                status = :status,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id
                        ");
                        
                        $result = $stmt->execute([
                            ':title' => $title,
                            ':slug' => $slug,
                            ':abstract' => $abstract,
                            ':content' => $content,
                            ':authors' => $authors,
                            ':publication_date' => $publication_date,
                            ':journal_name' => $journal_name,
                            ':volume' => $volume,
                            ':pages' => $pages,
                            ':status' => $status,
                            ':id' => $id
                        ]);
                        
                        if ($result) {
                            // Логируем действие
                            $logStmt = $pdo->prepare("
                                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details)
                                VALUES (:user_id, 'update', 'article', :entity_id, :details)
                            ");
                            $logStmt->execute([
                                ':user_id' => $_SESSION['admin_id'],
                                ':entity_id' => $id,
                                ':details' => json_encode(['title' => $title])
                            ]);
                            
                            $message = 'updated';
                        }
                    }
                }
            }
            break;
            
        case 'delete':
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                // Логируем действие
                $logStmt = $pdo->prepare("
                    INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details)
                    VALUES (:user_id, 'delete', 'article', :entity_id, :details)
                ");
                $logStmt->execute([
                    ':user_id' => $_SESSION['admin_id'],
                    ':entity_id' => $id,
                    ':details' => json_encode(['action' => 'delete'])
                ]);
            }
            header('Location: admin_articles.php');
            exit();
            
        case 'publish':
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE articles SET status = 'published' WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
            header('Location: admin_articles.php');
            exit();
            

     case 'list':
default:
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name 
        FROM articles a 
        LEFT JOIN users u ON a.created_by = u.id 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    }
   
    

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Статьи</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <?php include 'admin-header.php'; ?>
    
    <main class="admin-main">
        <?php if ($action === 'list'): ?>
        <!-- Список статей -->
        <div class="page-header">
            <a href="admin_articles.php">
                <h1><i class="fas fa-newspaper"></i> Управление статьями</h1>
            </a>
            <p>Количество статей: <?php echo count($articles); ?></p>
        </div>

        <?php if (isset($_GET['message']) && $_GET['message'] === 'created'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Статья успешно создана
        </div>
        <?php endif; ?>
        
        <div class="card mb-2">
            <div class="action-buttons">
                <a href="?action=new" class="btn btn-success">
                    <i class="fas fa-plus"></i> Добавить статью
                </a>
            </div>
        </div>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Авторы</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Создал</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Статьи не найдены</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($articles as $articleItem): ?>
                    <tr>
                        <td><?php echo $articleItem['id']; ?></td>
                        <td><?php echo htmlspecialchars($articleItem['title']); ?></td>
                        <td>
                            <?php 
                            $authors = json_decode($articleItem['authors'], true);
                            if (is_array($authors)) {
                                echo count($authors) . ' автор(ов)';
                            } else {
                                echo '0 авторов';
                            }
                            ?>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($articleItem['publication_date'])); ?></td>
                        <td>
                            <?php 
                            $statuses = [
                                'draft' => 'Черновик',
                                'under_review' => 'На рассмотрении',
                                'published' => 'Опубликовано'
                            ];
                            $status_text = $statuses[$articleItem['status']] ?? $articleItem['status'];
                            $status_class = '';
                            
                            if ($articleItem['status'] === 'published') {
                                $status_class = 'status-published';
                            } elseif ($articleItem['status'] === 'draft') {
                                $status_class = 'status-draft';
                            } else {
                                $status_class = 'status-review';
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($articleItem['first_name'] . ' ' . $articleItem['last_name']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <!-- Кнопка просмотра -->
                                <a href="../statiya.php?id=<?php echo $articleItem['id']; ?>" 
                                   target="_blank"
                                   class="btn btn-secondary" 
                                   title="Посмотреть на сайте">
                                   <i class="fas fa-eye"></i>
                                </a>
                                
                                <!-- Кнопка редактирования -->
                                <a href="?action=edit&id=<?php echo $articleItem['id']; ?>" 
                                   class="btn btn-primary"
                                   title="Редактировать статью">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Кнопка удаления -->
                                <a href="?action=delete&id=<?php echo $articleItem['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Вы уверены, что хотите удалить статью \'<?php echo addslashes($articleItem['title']); ?>\'? Это действие нельзя отменить.');"
                                   title="Удалить статью">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($action === 'edit'): ?>        
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Редактирование статьи</h1>
        </div>

        <?php if ($message === 'updated'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Статья успешно обновлена
        </div>
        <?php elseif ($message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="?action=edit&id=<?php echo $id; ?>">
                <div class="form-group">
                    <label for="title">Название статьи *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" 
                           value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>">
                    <small class="form-text">Если не указан, будет сгенерирован автоматически</small>
                </div>
                
                <div class="form-group">
                    <label for="authors">Авторы *</label>
                    <select id="authors" name="authors[]" multiple required style="height: 150px;">
                        <?php foreach ($allUsers as $u): ?>
                        <option value="<?php echo $u['id']; ?>"
                            <?php 
                            if (isset($article['authors'])) {
                                $authors = json_decode($article['authors'], true);
                                if (is_array($authors) && in_array($u['id'], $authors)) {
                                    echo ' selected';
                                }
                            }
                            ?>>
                            <?php echo htmlspecialchars($u['last_name'] . ' ' . $u['first_name'] . ' (' . $u['position'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких авторов</small>
                </div>
                
                <div class="form-group">
                    <label for="abstract">Аннотация *</label>
                    <textarea id="abstract" name="abstract" rows="5" required><?php echo htmlspecialchars($article['abstract'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Полный текст *</label>
                    <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="publication_date">Дата публикации</label>
                        <input type="date" id="publication_date" name="publication_date" 
                               value="<?php echo $article['publication_date'] ?? date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($article['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="under_review" <?php echo ($article['status'] ?? '') === 'under_review' ? 'selected' : ''; ?>>На рассмотрении</option>
                            <option value="published" <?php echo ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="journal_name">Название журнала</label>
                        <input type="text" id="journal_name" name="journal_name" 
                               value="<?php echo htmlspecialchars($article['journal_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="volume">Том</label>
                        <input type="text" id="volume" name="volume" 
                               value="<?php echo htmlspecialchars($article['volume'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="pages">Страницы</label>
                        <input type="text" id="pages" name="pages" 
                               value="<?php echo htmlspecialchars($article['pages'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="admin_articles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Обновить статью
                    </button>
                </div>
            </form>
        </div>

        <?php elseif ($action === 'new'): ?>
        <!-- СОЗДАНИЕ СТАТЬИ ФОРМА-->
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> Новая статья</h1>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="?action=new">
                <div class="form-group">
                    <label for="title">Название статьи *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" 
                           value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>">
                    <small class="form-text">Если не указан, будет сгенерирован автоматически</small>
                </div>
                
                <div class="form-group">
                    <label for="authors">Авторы *</label>
                    <select id="authors" name="authors[]" multiple required style="height: 150px;">
                        <?php foreach ($allUsers as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['last_name'] . ' ' . $u['first_name'] . ' (' . $u['position'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких авторов</small>
                </div>
                
                <div class="form-group">
                    <label for="abstract">Аннотация *</label>
                    <textarea id="abstract" name="abstract" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Полный текст *</label>
                    <textarea id="content" name="content" rows="15" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="publication_date">Дата публикации</label>
                        <input type="date" id="publication_date" name="publication_date" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft">Черновик</option>
                            <option value="under_review">На рассмотрении</option>
                            <option value="published">Опубликовано</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="journal_name">Название журнала</label>
                        <input type="text" id="journal_name" name="journal_name">
                    </div>
                    
                    <div class="form-group">
                        <label for="volume">Том</label>
                        <input type="text" id="volume" name="volume">
                    </div>
                    
                    <div class="form-group">
                        <label for="pages">Страницы</label>
                        <input type="text" id="pages" name="pages">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="admin_articles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>