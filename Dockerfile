FROM php:8.3-fpm-alpine

# Instala herramientas esenciales de Linux, Nginx, Supervisor y soporte para MySQL (TiDB)
RUN apk add --no-cache nginx supervisor default-mysql-client bash \
    && docker-php-ext-install pdo pdo_mysql pcntl

# Descarga e instalar la versión estable más reciente de Composer en el contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define el directorio de trabajo del servidor virtual
WORKDIR /var/www/html

# Copia todo el código de tu proyecto local hacia el contenedor de Hugging Face
COPY . .

# Instala todas las dependencias de Laravel y Swagger optimizadas para producción
RUN composer install --no-dev --optimize-autoloader

# Configura permisos obligatorios para que Laravel pueda escribir la caché, logs y subidas temporales
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copia las configuraciones específicas del servidor y el orquestador de procesos
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Otorga permisos de ejecución de Linux al script de arranque automatizado
RUN chmod +x /var/www/html/docker/docker-entrypoint.sh

# Expone el puerto obligatorio en la red interna que exige Hugging Face Spaces
EXPOSE 7860

# Ejecuta el script automatizador (correrá migraciones, compilará Swagger y encenderá Supervisor)
CMD ["/var/www/html/docker/docker-entrypoint.sh"]

