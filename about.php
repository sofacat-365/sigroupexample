<?php
$page_title = "О команде | Центр роботизированных решений";
require_once 'obschee/config.php';
require_once 'obschee/database.php';
require_once 'obschee/header.php';

// Получаем список сотрудников из базы данных
try {
    // Выбираем только активных сотрудников с ролью researcher или admin (для руководителя)
    $stmt = $pdo->query("SELECT id, first_name, last_name, position, email, bio, photo_url, social_links, role 
                         FROM users 
                         WHERE is_active = 1 
                         ORDER BY 
                            CASE role 
                                WHEN 'admin' THEN 1  -- Админ (руководитель) первый
                                WHEN 'researcher' THEN 2  -- Научные сотрудники
                                ELSE 3 
                            END,
                            last_name ASC");
    $team_members = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Ошибка загрузки данных: " . $e->getMessage();
    $team_members = [];
}
?>

<main class="container">
    <h1>О нашей команде</h1>

    <div class="team-description">
        <h2>Кто мы</h2>
        <p><strong>Центр роботизированных решений</strong> — это междисциплинарная исследовательская группа, объединяющая инженеров-робототехников, программистов, специалистов по компьютерному зрению и мехатроников. Мы создаем практичные роботизированные системы, которые решают реальные повседневные задачи — от автоматизации лабораторий до ухода за растениями.</p>
        
        <h2>Наша миссия</h2>
        <p><em>«Разрабатывать доступных, полезных и интуитивно понятных роботизированных помощников для решения повседневных задач, улучшения качества жизни и автоматизации рутинной научной работы».</em></p>
        <p>Мы верим, что роботы должны не пугать своей сложностью, а органично встраиваться в жизнь человека, беря на себя монотонные и трудоемкие задачи.</p>
        
        <h2>История группы</h2>
        <p>Группа была основана в 2020 году на базе университетской лаборатории робототехники. Начав с небольших студенческих проектов, за три года мы выросли в полноценный исследовательский центр, реализовавший более 10 прототипов роботов, опубликовавший 8 статей в рецензируемых журналах и установивший партнерские отношения с индустриальными компаниями.</p>
        <p>Ключевой момент нашего развития — победа в грантовом конкурсе в 2021 году, позволившая закупить современное оборудование и привлечь в команду талантливых инженеров.</p>
        
        <h2>Наши направления и цели</h2>
        <ul>
            <li><strong>Автономная мобильная робототехника</strong> — разработка алгоритмов навигации для безопасного перемещения роботов в динамической среде (лаборатории, склады, кампусы).</li>
            <li><strong>Компьютерное зрение и сенсорика</strong> — обучение нейросетей для распознавания и сортировки объектов, мониторинга состояния растений и инвентаризации.</li>
            <li><strong>Человеко-роботное взаимодействие</strong> — создание интуитивных интерфейсов, чтобы любой сотрудник (от лаборанта до библиотекаря) мог легко поставить задачу роботу.</li>
            <li><strong>Публикационная активность</strong> — делиться результатами с научным сообществом через открытые статьи и датасеты.</li>
            <li><strong>Трансфер технологий</strong> — внедрение наших разработок в реальные учреждения: больницы, тепличные комплексы, архивы.</li>
        </ul>

        <h2>Наши ценности</h2>
        <ul>
            <li><strong>Открытость</strong> — мы публикуем не только статьи, но и данные экспериментов, чтобы ускорить развитие робототехники в целом.</li>
            <li><strong>Практичность</strong> — каждый наш проект решает конкретную проблему, а не остается «роботом ради робота».</li>
            <li><strong>Коллаборация</strong> — мы активно сотрудничаем с другими институтами и индустриальными партнерами.</li>
        </ul>
    </div>

    <!-- Секция с ключевыми членами команды -->
    <div class="latest-section">
        <h2>Ключевые участники</h2>
        <div class="projects-grid">
            <?php if (!empty($team_members)): ?>
                <?php foreach ($team_members as $member): 
                    // Определяем эмодзи-заглушку в зависимости от роли
                    $avatar_fallback = '👨‍🔬'; // по умолчанию
                    if ($member['role'] == 'admin') {
                        $avatar_fallback = '👨‍🏫';
                    } elseif (strpos($member['first_name'], 'Мария') !== false || strpos($member['first_name'], 'Елена') !== false) {
                        $avatar_fallback = '👩‍💻';
                    } elseif ($member['position'] == 'Инженер-мехатроник') {
                        $avatar_fallback = '👨‍🔧';
                    }
                    
                    // Формируем полное имя
                    $full_name = $member['last_name'] . ' ' . $member['first_name'];
                    if ($member['role'] == 'admin') {
                        $full_name = 'Др. ' . $full_name;
                    }
                ?>
                <div class="project-card team-member-card">
                    <div class="member-photo">
                        <?php if (!empty($member['photo_url'])): ?>
                            <img src="<?= htmlspecialchars($member['photo_url']) ?>" alt="<?= htmlspecialchars($full_name) ?>">
                        <?php else: ?>
                            <div class="avatar-fallback"><?= $avatar_fallback ?></div>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($full_name) ?></h3>
                    <p class="member-role"><?= htmlspecialchars($member['position'] ?? 'Сотрудник') ?></p>
                    <p class="member-bio"><?= htmlspecialchars($member['bio'] ?? 'Нет описания') ?></p>
                    
                    <?php if (!empty($member['email'])): ?>
                    <p class="member-email">
                        <a href="mailto:<?= htmlspecialchars($member['email']) ?>" class="email-link">
                            ✉️ <?= htmlspecialchars($member['email']) ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    
                    <?php 
                    // Проверяем social_links (если есть в JSON формате)
                    $social_links = [];
                    if (!empty($member['social_links'])) {
                        if (is_string($member['social_links'])) {
                            $social_links = json_decode($member['social_links'], true) ?? [];
                        } elseif (is_array($member['social_links'])) {
                            $social_links = $member['social_links'];
                        }
                    }
                    ?>
                    
                    <?php if (!empty($social_links)): ?>
                    <div class="social-icons">
                        <?php foreach ($social_links as $platform => $link): ?>
                            <?php 
                            $icon = '🔗'; // по умолчанию
                            if ($platform == 'linkedin') $icon = 'in';
                            elseif ($platform == 'github') $icon = 'gh';
                            elseif ($platform == 'twitter') $icon = '𝕏';
                            ?>
                            <a href="<?= htmlspecialchars($link) ?>" target="_blank" class="social-icon"><?= $icon ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Информация о команде временно недоступна</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Призыв к действию -->
    <div class="cta-section">
        <h2>Хотите присоединиться или сотрудничать?</h2>
        <p>Мы всегда открыты для новых талантов и партнеров.</p>
        <div class="cta-buttons">
            <a href="contacts.php" class="btn">Связаться с нами</a>
            <a href="projects.php" class="btn btn-secondary">Посмотреть проекты</a>
        </div>
    </div>
</main>

<?php require_once 'obschee/footer.php'; ?>