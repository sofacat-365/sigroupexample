    </main> <!-- закрываем main из header.php -->
    <!-- Подвал сайта -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Контакты</h3>
                    <p>Email: contact@sigroup.ru</p>
                    <p>Телефон: +7 (912) 345-67-89</p>
                </div>
                
                <div class="footer-section">
                    <h3>Быстрые ссылки</h3>
                    <ul>
                        <li><a href="/index.php">Главная</a></li>
                        <li><a href="/statii.php">Статьи</a></li>
                        <li><a href="/contacts.php">Контакты</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. Все права защищены.</p>
            </div>
        </div>
    </footer>
    
    <!-- Подключаем JavaScript -->
    <script src="obschee/assets/js/script.js"></script>
</body>
</html>