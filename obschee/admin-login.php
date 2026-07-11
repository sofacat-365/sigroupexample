<?php 
session_start();
require_once 'connect.php';
?>

<!DOCTYPE html>
<html>
<head>

    <title>Админка</title>
        <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            border-color: #4a6fa5;
            outline: none;
        }
        
        input[type="text"]::placeholder {
            color: #999;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            border-color: #4a6fa5;
            outline: none;
        }
        
        input[type="password"]::placeholder {
            color: #999;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4a6fa5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        
        button[type="submit"]:hover {
            background-color: #3a5a80;
        }
        
        button[type="submit"]:active {
            transform: translateY(1px);
        }
    </style>
</head>
<body class="admin-login-container">
    <form class="admin-login-form" action="" method="post">
        <h2 class="admin-login-title">Админка</h2>
        <div class="admin-input-group">
            <input type="text" placeholder="Введите логин" name="login">
        </div>
        <div class="admin-input-group">
            <input type="password" placeholder="Введите пароль" name="password">
        </div>
        <button type="submit" class="admin-login-button">Вход</button>
    </form>

<?php
if (isset($_POST['login'])) {
    $login = $_POST["login"];
    $password = $_POST["password"];
    
    $sql = $pdo->prepare("SELECT id, login FROM users WHERE login=:login AND password=:password");
    $sql->execute(array('login' => $login, 'password' => $password));
    $array = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($array && $array["id"] > 0) {
        $_SESSION['login'] = $array["login"];
        header('Location: admin-data.php');
        exit();
    } else {
        echo "Неверный логин или пароль!";
    }
}
?>
</body>
</html>

<!-- <php
// admin/admin-login.php
// session_start();
// require_once 'config.php';
// require_once 'db.php';

// $error = '';

// // Обработка формы входа
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $users = trim($_POST['users'] ?? '');
//     $password = trim($_POST['password'] ?? '');
    
//     if (!empty($username) && !empty($password)) {
//         try {
//             // Создаем подключение к БД
//             $db = new Database();
//             $conn = $db->getConnection();
            
//             // Ищем пользователя
//             $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE (username = :username OR email = :username) AND is_active = 1");
//             $stmt->execute([':username' => $username]);
//             $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
//             if ($user && password_verify($password, $user['password'])) {
//                 // Проверяем роль пользователя
//                 if (in_array($user['role'], ['admin', 'editor'])) {
//                     // Успешный вход
//                     $_SESSION['admin_logged_in'] = true;
//                     $_SESSION['admin_id'] = $user['id'];
//                     $_SESSION['admin_username'] = $users['users'];
//                     $_SESSION['admin_role'] = $user['role'];
//                     $_SESSION['admin_login_time'] = time();
                    
//                     // Логируем вход
//                     $logStmt = $conn->prepare("
//                         INSERT INTO activity_logs (user_id, action, entity_type, details, ip_address, user_agent)
//                         VALUES (:user_id, 'login', 'system', :details, :ip, :agent)
//                     ");
//                     $logStmt->execute([
//                         ':user_id' => $user['id'],
//                         ':details' => json_encode(['type' => 'admin_login']),
//                         ':ip' => $_SERVER['REMOTE_ADDR'],
//                         ':agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
//                     ]);
                    
//                     header('Location: admin-dashboard.php');
//                     exit();
//                 } else {
//                     $error = 'Недостаточно прав для доступа в админ-панель';
//                 }
//             } else {
//                 $error = 'Неверное имя пользователя или пароль';
//             }
//         } catch (PDOException $e) {
//             $error = 'Ошибка подключения к базе данных';
//             error_log("Login error: " . $e->getMessage());
//         }
//     } else {
//         $error = 'Заполните все поля';
//     }
// }
// ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-lock"></i> Админ-панель</h1>
                <p>Научная группа - Управление контентом</p>
            </div>
            
            <php if ($error): ?>
            <div class="error"><php echo htmlspecialchars($error); ?></div>
            <php endif; ?>
            
            <div class="login-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Имя пользователя или Email</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        Войти в админ-панель
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> -->