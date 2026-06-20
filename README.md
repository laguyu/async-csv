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

El proyecto está dockerizado y estructurado bajo una arquitectura desacoplada pura, corriendo de forma continua y 100% gratuita en la nube de **Hugging Face Spaces**, utilizando **TiDB Serverless (MySQL)** como base de datos externa distribuida y **Swagger UI** nativo para pruebas interactivas.

---

## 🧠 Retos Técnicos Resueltos (Optimización Backend)

1. **Eficiencia en Memoria RAM (Streaming):** En lugar de cargar un archivo de 20MB o 50MB entero en la memoria (lo que tumbaría el servidor), el sistema abre un puntero de lectura directa (`fopen`) y procesa el archivo línea por línea.
2. **Optimización de Consultas (Chunking & Upsert):** En lugar de hacer miles de inserciones individuales a la base de datos (cuello de botella), el sistema agrupa los productos en bloques de 500 (*Chunks*) y los guarda en una sola consulta masiva utilizando la sentencia `upsert`. Si el producto es nuevo lo crea; si el SKU existe, actualiza los datos.
3. **Procesamiento Asíncrono (Colas de Trabajo):** El servidor web recibe el archivo, responde de inmediato al cliente con un código `202 Accepted` (en milisegundos) y delega la tarea pesada a un proceso independiente que corre en segundo plano (*Background Worker*).
4. **Notificación por Consulta (Polling):** Para entornos de alta disponibilidad y serverless, el cliente puede consultar un endpoint específico utilizando el ID de importación para ver el porcentaje de avance matemático (`0%` a `100%`) en tiempo real.
5. **Infraestructura Escalable (Tuning de Servidores):** Se configuraron los límites de carga de red tanto en el servidor web **Nginx** (`client_max_body_size`) como en el motor **PHP** (`upload_max_filesize`), permitiendo transferencias pesadas de archivos masivos de datos sin bloqueos de infraestructura.

---

## 🏗️ Arquitectura y Principios SOLID Aplicados (Desacoplamiento Puro)

El proyecto fue estructurado bajo los principios de diseño de software **SOLID** de manera estricta para garantizar un código libre de dependencias rígidas de terceros:

* **S - Single Responsibility (Responsabilidad Única):** Tus controladores de Laravel están 100% limpios y enfocados únicamente en su responsabilidad HTTP (recibir parámetros y despachar tareas). No contienen código basura de anotaciones de documentación externas.
* **Desacoplamiento de Infraestructura:** La documentación se maneja de forma independiente a través de un contrato estático **OpenAPI (JSON)** ubicado en la capa pública. Si el día de mañana decides migrar la documentación a herramientas como Postman, Redoc o Stoplight, tu código backend de PHP no sufre ninguna modificación.
* **D - Dependency Inversion (Inversión de Dependencias):** El Job no depende de una clase de código rígida, sino de una **Interfaz (Contrato)**. El contenedor de Laravel inyecta el servicio de forma dinámica a través del `AppServiceProvider`.

---

## 🛠️ Tecnologías Utilizadas

* **Backend Core:** PHP 8.3 / Laravel 11+
* **Patrones:** Service Pattern & Contracts (Interfaces)
* **Documentación:** Swagger UI (Integrado nativamente vía CDN en Blade + OpenAPI 3.0 Especificación Estática)
* **Contenedores & DevOps:** Docker / Supervisor / Nginx / GitHub Actions (CI/CD)
* **Base de Datos:** MySQL (TiDB Serverless Cloud con Conexión Segura SSL)

---

## 📋 Estructura Obligatoria del Archivo CSV

Para realizar pruebas con éxito, el archivo de importación debe ser un archivo de texto plano separado por comas (`.csv`), y la primera línea **debe contener exactamente los siguientes nombres de columna en minúsculas**:

```text
sku,name,description,price,stock
```

### Ejemplo de contenido válido para pruebas:
```text
sku,name,description,price,stock
PROD-001,Laptop Gamer ASUS,Procesador Ryzen 7 y 16GB RAM,1250.00,15
PROD-002,Teclado Mecanico RGB,Teclado con switches red silenciosos,85.50,50
PROD-003,Mouse Inalambrico,Mouse ergonomico para oficina,29.99,100
PROD-004,Monitor 4K 27 pulgadas,Monitor IPS ideal para diseno,399.00,8
PROD-005,Audifonos HyperX,,45.00,45
```
*Nota: La descripción es opcional (puede ir vacía como en el PROD-005). Los precios deben utilizar punto decimal (`.`) y no comas.*

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

El proyecto funciona de forma autónoma en la infraestructura de **Hugging Face Spaces**. Al subir el código mediante la automatización de **GitHub Actions**, la plataforma lee el `Dockerfile` e instala todo el entorno de producción.

### Estructura de Archivos de Infraestructura Incluidos:
* **`Dockerfile`**: Configura una imagen Linux Alpine con PHP-FPM, Nginx, Supervisor y las extensiones necesarias para MySQL (`pdo_mysql`, `pcntl`). Instala los certificados `ca-certificates` del sistema operativo para permitir el túnel seguro hacia la base de datos distribuida de TiDB Cloud.
* **`docker/nginx.conf`**: Configura el servidor web ampliando el parámetro `client_max_body_size 50M` para admitir cargas pesadas.
* **`docker/uploads.ini`**: Inyecta configuraciones al núcleo de PHP (`upload_max_filesize` y `post_max_size` a 50M) alineando el backend con los límites del servidor.
* **`docker/supervisord.conf`**: El orquestador que mantiene encendidos simultáneamente Nginx, PHP y el comando de colas de Laravel de manera ininterrumpida las 24 horas del día.
* **`docker/docker-entrypoint.sh`**: Script de arranque automatizado en la nube que ejecuta de forma segura las migraciones pendientes en TiDB Cloud usando SSL.

---

## 🧪 Pruebas Interactivas en la Nube (Endpoints)

Puedes probar la API en producción e interactuar con los endpoints ingresando directamente a la interfaz gráfica independiente a pantalla completa:

👉 <a href="https://wilanmonlo-async-csv-api.hf.space" target="_blank"><b>Probar API en Vivo (Swagger UI)</b></a>

### Flujo de Prueba de la API:
1. **`POST /api/products/import` (Subir Catálogo):** Haz clic en *Try it out*, selecciona tu archivo `.csv` estructurado (siguiendo la guía de este README) y presiona *Execute*. El servidor responderá de inmediato con código `202 Accepted` y un JSON con el identificador de tu proceso (ej: `"import_id": 1`).
2. **`GET /api/products/import/{id}` (Monitorear Progreso):** Coloca el ID generado en este endpoint y presiona *Execute*. Si consultas continuamente, verás en tiempo real cómo el estado cambia de `pending` a `processing` mientras el contador de filas (`processed_rows`) y el porcentaje de progreso matemático (`progress`) aumentan en bloques de 500 en 500 hasta marcar `100% completed`.
