---
title: Async CSV API
emoji: 🚀
colorFrom: indigo
colorTo: pink
sdk: docker
app_port: 7860
pinned: false
---

# 🚀 API de Procesamiento Asíncrono de Catálogos (CSV) con SOLID & Docker

Esta es una API de alto rendimiento desarrollada en **Laravel** diseñada para resolver un problema crítico en sistemas empresariales y de comercio electrónico: la importación masiva de productos mediante archivos CSV de gran tamaño sin saturar la memoria del servidor ni bloquear la experiencia del usuario.

El proyecto está dockerizado y preparado para correr de forma continua y 100% gratuita en **Hugging Face Spaces**, utilizando **TiDB Serverless (MySQL)** como base de datos externa y **Swagger** para pruebas interactivas en tiempo real.

---

## 🧠 Retos Técnicos Resueltos (Optimización Backend)

1. **Eficiencia en Memoria RAM (Streaming):** En lugar de cargar un archivo de 20MB o 50MB entero en la memoria (lo que tumbaría el servidor), el sistema abre un puntero de lectura directa (`fopen`) y procesa el archivo línea por línea.
2. **Optimización de Consultas (Chunking & Upsert):** En lugar de hacer miles de inserciones individuales a la base de datos (cuello de botella), el sistema agrupa los productos en bloques de 500 (*Chunks*) y los guarda en una sola consulta masiva utilizando la sentencia `upsert`. Si el producto es nuevo lo crea; si el SKU existe, actualiza los datos.
3. **Procesamiento Asíncrono (Colas de Trabajo):** El servidor web recibe el archivo, responde de inmediato al cliente con un código `202 Accepted` (en milisegundos) y delega la tarea pesada a un proceso independiente que corre en segundo plano (*Background Worker*).
4. **Notificación por Consulta (Polling):** Para entornos de alta disponibilidad y serverless, el cliente puede consultar un endpoint específico utilizando el ID de importación para ver el porcentaje de avance matemático (`0%` a `100%`) en tiempo real.

---

## 🏗️ Arquitectura y Principios SOLID Aplicados

El proyecto fue estructurado bajo los principios de diseño de software **SOLID** para garantizar un código mantenible, desacoplado y escalable:

* **S - Single Responsibility (Responsabilidad Única):** Cada clase hace una sola cosa. El *Form Request* valida el archivo físico, el *Controlador* solo recibe la petición HTTP, el *Job* maneja la infraestructura de la cola, el *Servicio* ejecuta la lógica de negocio y el *Modelo* define la accesibilidad de datos.
* **O - Open/Closed (Abierto/Cerrado):** El sistema está abierto a expandirse para nuevos formatos de archivos (como XML o JSON) mediante la implementación de contratos, pero cerrado a modificaciones en sus componentes Core.
* **D - Dependency Inversion (Inversión de Dependencias):** El Job no depende de una clase de código rígida, sino de una **Interfaz (Contrato)**. El contenedor de Laravel inyecta el servicio de forma dinámica a través del `AppServiceProvider`.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.3 / Laravel 11+
* **Patrones:** Service Pattern & Contracts (Interfaces)
* **Documentación:** Swagger (L5-Swagger con OpenAPI 3.0)
* **Contenedores & DevOps:** Docker / Supervisor / Nginx
* **Base de Datos:** MySQL (TiDB Serverless Cloud)

---

## 💻 Cómo Ejecutar y Probar el Proyecto Localmente

### Prerrequisitos
* Tener instalado **PHP 8.2+**, **Composer** y un gestor de bases de datos local (Laragon, XAMPP, etc.).

### 1. Instalación Inicial
```bash
git clone https://github.com
cd TU_REPOSITORIO
composer install
cp .env.example .env
php artisan key:generate
```
*Configura las credenciales de tu base de datos local en el archivo `.env`.*

### 2. Tablas y Datos de Prueba
Crea las tablas del sistema e internas de las colas, y genera un archivo CSV de prueba con **20,000 productos ficticios** en segundos:
```bash
php artisan migrate
php artisan db:seed --class=CsvTestGeneratorSeeder
```

### 3. Ejecución del Sistema
Abre **dos terminales independientes**:
* **Terminal 1 (Servidor API):** `php artisan serve` (Inicia en `http://127.0.0.1:8000`)
* **Terminal 2 (Procesador de Colas):** `php artisan queue:work`

---

## 🐳 Despliegue en la Nube con Docker & Hugging Face Spaces

El proyecto está diseñado para funcionar de forma autónoma en **Hugging Face Spaces**. Al subir el código, la plataforma lee el `Dockerfile` e instala todo el entorno de producción. Cada vez que el contenedor se enciende, ejecuta un script (`docker-entrypoint.sh`) que corre las migraciones en TiDB Cloud y autogenera la documentación de Swagger.

### Estructura de Archivos de Infraestructura Incluidos:
* **`Dockerfile`**: Configura una imagen Linux Alpine con PHP-FPM, Nginx, Supervisor y las extensiones necesarias para MySQL (`pdo_mysql`, `pcntl`).
* **`docker/nginx.conf`**: Configura el servidor web en el puerto `7860` requerido por Hugging Face.
* **`docker/supervisord.conf`**: El guardián de procesos que mantiene encendidos simultáneamente Nginx, PHP y el comando de colas de Laravel.
* **`docker/docker-entrypoint.sh`**: Script de arranque automatizado en la nube.

### Pasos para Desplegar:
1. Crea un clúster gratuito en [TiDB Cloud (Serverless)](https://pingcap.com).
2. Ve a la consola de TiDB, genera una nueva contraseña de conexión para **MySQL** y copia los datos correspondientes (`Host`, `Database`, `User`, `Port` y `Password`).
3. Crea un **Space** nuevo en [Hugging Face](https://huggingface.co/), selecciona **Docker** como SDK y elige la plantilla **Blank** (Pública).
4. En la pestaña **Settings** de tu Space, ve a **Variables and Secrets** y añade las variables de producción utilizando los datos de TiDB:
   * `APP_ENV` = `production` | `APP_DEBUG` = `false` | `APP_KEY` = `base64:...`
   * `DB_CONNECTION` = `mysql`
   * `DB_HOST` = *(Host de TiDB Cloud)* | `DB_PORT` = `4000`
   * `DB_DATABASE` = *(Tu base de datos)* | `DB_USERNAME` = *(Tu usuario de TiDB)* | `DB_PASSWORD` = *(Tu contraseña)*
   * `QUEUE_CONNECTION` = `database`
5. Sube tu código al repositorio del Space. ¡Hugging Face compilará y activará la API automáticamente!

---

## 🧪 Pruebas Interactivas con Swagger (Endpoints)

Cualquier reclutador o cliente puede probar la API en producción ingresando directamente a la interfaz gráfica interactiva de Swagger añadida al despliegue:

```text
https://huggingface.co
```

### Flujo de Prueba:
1. **`POST /api/products/import` (Subir Catálogo):** Haz clic en *Try it out*, selecciona el archivo `productos.csv` de prueba y presiona *Execute*. El servidor responderá de inmediato con código `202 Accepted` y un JSON con el identificador (ej: `"import_id": 1`).
2. **`GET /api/products/import/{id}` (Monitorear Progreso):** Coloca el ID generado en este endpoint y presiona *Execute*. Si consultas continuamente, verás en tiempo real cómo el estado cambia de `pending` a `processing` mientras el contador de filas (`processed_rows`) y el porcentaje de progreso (`progress`) aumentan en bloques de 500 en 500 hasta marcar `100% completed`.
