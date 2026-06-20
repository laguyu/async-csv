#!/bin/sh

# Espera a que la red de la nube esté lista
sleep 5

# 1. Crea las tablas automáticamente en Supabase
php /var/www/html/artisan migrate --force

# 2. Inicia el guardián de procesos (Nginx, PHP y las Colas)
exec /usr/bin/supervisord -c /etc/supervisord.conf
