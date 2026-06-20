FROM php:8.3-fpm-alpine

# CORRECCIÓN: Cambiado default-mysql-client por mariadb-client
RUN apk add --no-cache nginx supervisor mariadb-client bash \
    && docker-php-ext-install pdo pdo_mysql pcntl

# Descargar e instalar la versión estable más reciente de Composer en el contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir el directorio de trabajo del servidor virtual
WORKDIR /var/www/html

# Copiar todo el código de tu proyecto local hacia el contenedor de Hugging Face
COPY . .

# Instalar todas las dependencias de Laravel y Swagger optimizadas para producción
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos obligatorios para que Laravel pueda escribir la caché, logs y subidas temporales
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar las configuraciones específicas del servidor y el orquestador de procesos
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Otorgar permisos de ejecución de Linux al script de arranque automatizado
RUN chmod +x /var/www/html/docker/docker-entrypoint.sh

# Exponer el puerto obligatorio en la red interna que exige Hugging Face Spaces
EXPOSE 7860

# Ejecutar el script automatizador (correrá migraciones, compilará Swagger y encenderá Supervisor)
CMD ["/var/www/html/docker/docker-entrypoint.sh"]
