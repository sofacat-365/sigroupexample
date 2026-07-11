<?php
$page_title = "Контакты";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name)) {
        $errors[] = "Введите ваше имя";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }
    
    if (empty($subject)) {
        $errors[] = "Введите тему сообщения";
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Сообщение должно содержать не менее 10 символов";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages 
                                  (name, email, subject, message, ip_address, user_agent) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $name,
                $email,
                $subject,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            $success = true;
            
        } catch (PDOException $e) {
            $errors[] = "Ошибка при отправке сообщения";
        }
    }
}
?>

<main class="container">
    <div class="data-header">
        <h1>Контакты</h1>
    </div>

    <div class="contacts-container">
        <div class="contact-form-container">
            <h2>Контактная информация</h2>
            
            <div class="contact-details">
                <div class="contact-item">
                    <h3>Email</h3>
                    <p>
                        <a href="mailto:contact@research-group.ru">
                            contact@research-group.ru
                        </a>
                    </p>
                </div>
                
                <div class="contact-item">
                    <h3>Телефон</h3>
                    <p>+7 (912) 345-67-89</p>
                </div>
                
                <div class="contact-item">
                    <h3>Адрес</h3>
                    <p>ул. Научная, д. 1, г. Наукоград</p>
                </div>
            </div>
            <div class="social-links">
                <h3>Мы в социальных сетях</h3>
                <div class="social-icons">
                    <a href="https://twitter.com/research_group" 
                       target="_blank" class="social-icon twitter">
                        Twitter
                    </a>
                    
                    <a href="https://github.com/research-group" 
                       target="_blank" class="social-icon github">
                        GitHub
                    </a>
                    
                    <a href="https://scholar.google.com" 
                       target="_blank" class="social-icon scholar">
                        Google Scholar
                    </a>
                </div>
            </div>
        </div>
        <div class="contact-form-container">
            <h2>Связаться с нами</h2>
            
            <?php if ($success): ?>
                <div class="alert success">
                    <p>Спасибо! Ваше сообщение отправлено. Мы ответим вам в ближайшее время.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Ваше имя *</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Тема сообщения *</label>
                    <input type="text" id="subject" name="subject" 
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="message">Сообщение *</label>
                    <textarea id="message" name="message" rows="6" required><?= 
                        htmlspecialchars($_POST['message'] ?? '') 
                    ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Отправить сообщение</button>
                </div>
                
                <p class="form-note">* Поля, обязательные для заполнения</p>
            </form>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>