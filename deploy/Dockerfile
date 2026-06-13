FROM thiagobarros19/laravel-base:latest

LABEL realizalab-api.version="1.0.0"
LABEL maintainer="Thiago Barros"
LABEL realizalab-api.release-date="2025-01-08"

COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Instala dependências do PHP
RUN composer install --no-dev --prefer-dist

# Permissões para Laravel
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/html/storage/app/public \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage

# Permissões para o vendor do Laravel
RUN chown -R www-data:www-data /var/www/html/vendor \
    && chmod -R 775 /var/www/html/vendor

# Copia configurações do Nginx e PHP
COPY ./deploy/php.ini "$PHP_INI_DIR/conf.d/app.ini"
COPY ./deploy/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./deploy/supervisor/supervisord.conf /etc/supervisord.conf

# Otimizações do Laravel
RUN php artisan optimize

# Comando de inicialização via Supervisord (gerencia nginx, php-fpm e queue worker)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
