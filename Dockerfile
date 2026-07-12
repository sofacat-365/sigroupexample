FROM php:8.2-apache

# Расширения PHP, нужные проекту (PDO MySQL) + mod_rewrite для .htaccess
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Разрешаем .htaccess (в php:apache образе AllowOverride по умолчанию None)
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-available/override.conf \
    && a2enconf override

WORKDIR /var/www/html

COPY . /var/www/html

# Папки под загрузки должны быть доступны на запись веб-серверу
RUN mkdir -p /var/www/html/upload /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/upload /var/www/html/uploads

EXPOSE 80
