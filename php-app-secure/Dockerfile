FROM php:8.0-apache

# 1) PDO/MySQL
RUN docker-php-ext-install pdo pdo_mysql

# 2) Включаем rewrite
RUN a2enmod rewrite

# 3) Меняем корневую папку и разрешаем доступ к ней через .htaccess
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && printf "\n<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n" \
       >> /etc/apache2/apache2.conf

# 4) Копируем проект
COPY . /var/www/html

# 5) Права
RUN chown -R www-data:www-data /var/www/html
