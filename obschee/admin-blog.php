<?php
session_start();
require_once 'connect.php';  
if (!isset($_SESSION['login'])) {
    header('Location: admin-login.php');
    exit();
}
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $tags_input = $_POST['tags'] ?? [];
    if (empty($title) || empty($excerpt) || empty($content)) {
        $error = 'Заполните все обязательные поля (заголовок, краткое описание, текст)';
    } else {
        $slug = generateSlug($title);
        
        $tags_array = [];
        if (!empty($tags_input) && is_array($tags_input)) {
            foreach ($tags_input as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $tags_array[] = $tag;
                }
            }
        }
        $tags_json = json_encode($tags_array, JSON_UNESCAPED_UNICODE);
        $author_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 
        
        if ($id > 0) {
            $sql = "UPDATE blog_posts SET 
                    title = ?, slug = ?, excerpt = ?, content = ?, tags = ?, status = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $excerpt, $content, $tags_json, $status, $id]);
            
            $message = 'Запись блога обновлена успешно!';
        } else {
            $sql = "INSERT INTO blog_posts 
                    (title, slug, excerpt, content, author_id, tags, status, published_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : NULL;
            $stmt->execute([$title, $slug, $excerpt, $content, $author_id, $tags_json, $status, $published_at]);
            $new_id = $pdo->lastInsertId();
            
            $message = 'Запись блога создана успешно!';
            $id = $new_id;
            $action = 'edit';
            $sql = "SELECT * FROM blog_posts WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $post_data = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

if ($action === 'delete' && $id > 0) {
    $sql = "DELETE FROM blog_posts WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    $message = 'Запись блога удалена успешно!';
    $action = 'list';
}

$post_data = [];
if ($action === 'edit' && $id > 0) {
    $sql = "SELECT * FROM blog_posts WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $post_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post_data) {
        $error = 'Запись не найдена!';
        $action = 'list';
    }
}
if ($action === 'list') {
    $sql = "SELECT bp.*, u.first_name, u.last_name 
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            ORDER BY bp.created_at DESC 
            LIMIT 50";
    $posts = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
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
    <title>Админ-панель | Блог</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="page-header">            
            <a href="admin-blog.php">
                <h1><i class="fas fa-blog"></i> Управление блогом</h1>
            </a>
            <p>Создание и редактирование новостей и анонсов</p>
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
        <div class="card mb-2">
            <div class="action-buttons">
                <a href="?action=new" class="btn btn-success">
                    <i class="fas fa-plus"></i> Новая запись
                </a>
            </div>
        </div>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Автор</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Просмотры</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $post['status'] === 'published' ? 'status-published' : 'status-draft'; ?>">
                                <?php echo $post['status'] === 'published' ? 'Опубликовано' : 'Черновик'; ?>
                            </span>
                        </td>
                        <td><?php echo $post['views_count']; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Удалить эту запись?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="../blog.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-secondary">
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
        <!-- Форма создания/редактирования записи -->
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> 
                <?php echo $action === 'new' ? 'Новая запись блога' : 'Редактирование записи'; ?>
            </h1>
        </div>
        
        <div class="card">
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($id > 0): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Заголовок *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($post_data['title'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($post_data['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="published" <?php echo ($post_data['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="excerpt">Краткое описание *</label>
                    <textarea id="excerpt" name="excerpt" rows="3" required><?php echo htmlspecialchars($post_data['excerpt'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Полный текст *</label>
                    <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($post_data['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="tags">Теги (выберите один или несколько)</label>
                    <select id="tags" name="tags[]" multiple style="height: 150px;">
                        <?php 
                        $all_tags = [];
                        $tags_query = $pdo->query("SELECT tags FROM blog_posts WHERE tags IS NOT NULL AND tags != ''");
                        
                        while ($row = $tags_query->fetch(PDO::FETCH_ASSOC)) {
                            if (!empty($row['tags'])) {
                                $decoded = json_decode($row['tags'], true);
                                if (is_array($decoded)) {
                                    foreach ($decoded as $tag) {
                                        if (!empty($tag) && !in_array($tag, $all_tags)) {
                                            $all_tags[] = trim($tag);
                                        }
                                    }
                                }
                            }
                        }
                        
                        $all_tags = array_unique($all_tags);
                        sort($all_tags);
                        
                        $current_tags = [];
                        if (!empty($post_data['tags'])) {
                            $current_decoded = json_decode($post_data['tags'], true);
                            if (is_array($current_decoded)) {
                                $current_tags = $current_decoded;
                            }
                        }
                        foreach ($all_tags as $tag): ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>"
                                <?php echo in_array($tag, $current_tags) ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($tag); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких тегов</small>
                </div>
                
                <div class="form-actions">
                    <a href="?" class="btn btn-secondary">
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