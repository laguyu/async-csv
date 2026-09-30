---
title: Async CSV API
emoji: 🚀
colorFrom: indigo
colorTo: pink
sdk: docker
app_port: 7860
pinned: false
---

# 🚀 Asynchronous CSV Catalog Processing API with SOLID & Docker

This is a high-performance API built with **Laravel** to solve a critical problem in enterprise and e-commerce systems: importing large numbers of products from CSV files without exhausting server memory or blocking the user experience.

The project is containerized and built on a fully decoupled architecture. It runs continuously and free of charge in the **Hugging Face Spaces** cloud, using **TiDB Serverless (MySQL)** as an external distributed database and native **Swagger UI** for interactive testing.

---

## 🧠 Technical Challenges Solved (Backend Optimization)

1. **Memory Efficiency (Streaming):** Instead of loading an entire 20 MB or 50 MB file into memory, which could bring down the server, the system opens a direct read handle (`fopen`) and processes the file line by line.
2. **Query Optimization (Chunking & Upsert):** Instead of making thousands of individual database inserts, the system groups products into batches of 500 (*chunks*) and saves them in a single bulk query using `upsert`. New products are inserted; existing products with the same SKU are updated.
3. **Asynchronous Processing (Job Queues):** The web server receives the file, immediately responds to the client with a `202 Accepted` status (within milliseconds), and delegates the heavy work to an independent background process (*background worker*).
4. **Progress Notifications (Polling):** In high-availability and serverless environments, the client can query a specific endpoint using the import ID to see the calculated progress percentage (`0%` to `100%`) in real time.
5. **Scalable Infrastructure (Server Tuning):** Upload limits are configured on both the **Nginx** web server (`client_max_body_size`) and the **PHP** engine (`upload_max_filesize`), allowing large data files to be transferred without infrastructure bottlenecks.

---

## 🏗️ Architecture and SOLID Principles (Pure Decoupling)

The project strictly follows **SOLID** software design principles to ensure the code is free from rigid third-party dependencies:

* **S - Single Responsibility:** Laravel controllers are focused entirely on their HTTP responsibilities: receiving parameters and dispatching tasks. They contain no external documentation annotations.
* **Infrastructure Decoupling:** Documentation is managed independently through a static **OpenAPI (JSON)** contract in the public layer. If you later migrate the documentation to tools such as Postman, Redoc, or Stoplight, the PHP backend code requires no changes.
* **D - Dependency Inversion:** The job depends on an **interface (contract)** rather than a concrete class. Laravel's container dynamically injects the service through the `AppServiceProvider`.

---

## 🛠️ Technologies Used

* **Backend Core:** PHP 8.3 / Laravel 11+
* **Patrones:** Service Pattern & Contracts (Interfaces)
* **Documentation:** Swagger UI (natively integrated via CDN in Blade + static OpenAPI 3.0 specification)
* **Contenedores & DevOps:** Docker / Supervisor / Nginx / GitHub Actions (CI/CD)
* **Database:** MySQL (TiDB Serverless Cloud with a secure SSL connection)

---

## 📋 Required CSV File Structure

For successful imports, the file must be a comma-separated plain-text file (`.csv`), and the first line **must contain exactly the following lowercase column names**:

```text
sku,name,description,price,stock
```

### Example of valid test data:
```text
sku,name,description,price,stock
PROD-001,ASUS Gaming Laptop,Ryzen 7 processor and 16 GB RAM,1250.00,15
PROD-002,Mechanical RGB Keyboard,Keyboard with quiet red switches,85.50,50
PROD-003,Wireless Mouse,Ergonomic office mouse,29.99,100
PROD-004,27-inch 4K Monitor,IPS monitor ideal for design,399.00,8
PROD-005,HyperX Headphones,,45.00,45
```
*Note: The description is optional and can be left blank, as in PROD-005. Prices must use a decimal point (`.`), not commas.*

---

## 💻 Running and Testing the Project Locally

### Prerequisites
* **PHP 8.2+**, **Composer**, and a local database manager (Laragon, XAMPP, etc.).

### 1. Initial Setup
```bash
git clone https://github.com
cd TU_REPOSITORIO
composer install
cp .env.example .env
php artisan key:generate
```
*Configure your local database credentials in the `.env` file.*

### 2. Tables and Test Data
Create the system and queue tables, then generate a test CSV file containing **20,000 fictional products** in seconds:
```bash
php artisan migrate
php artisan db:seed --class=CsvTestGeneratorSeeder
```

### 3. Running the System
Open **two separate terminals**:
* **Terminal 1 (API server):** `php artisan serve` (starts at `http://127.0.0.1:8000`)
* **Terminal 2 (Queue worker):** `php artisan queue:work`

---

## 🐳 Cloud Deployment with Docker & Hugging Face Spaces

The project runs autonomously on **Hugging Face Spaces** infrastructure. When code is pushed through **GitHub Actions** automation, the platform reads the `Dockerfile` and installs the entire production environment.

### Included Infrastructure Files:
* **`Dockerfile`**: Configures an Alpine Linux image with PHP-FPM, Nginx, Supervisor, and the extensions required for MySQL (`pdo_mysql`, `pcntl`). It installs the operating system's `ca-certificates` to enable a secure connection to the TiDB Cloud distributed database.
* **`docker/nginx.conf`**: Configures the web server with `client_max_body_size 50M` to support large uploads.
* **`docker/uploads.ini`**: Sets PHP configuration values (`upload_max_filesize` and `post_max_size` to 50M), aligning the backend with the server limits.
* **`docker/supervisord.conf`**: Keeps Nginx, PHP, and Laravel's queue worker running continuously.
* **`docker/docker-entrypoint.sh`**: An automated cloud startup script that securely runs pending migrations on TiDB Cloud using SSL.

---

## 🧪 Interactive Cloud API Testing (Endpoints)

You can test the production API and interact with its endpoints directly through the standalone, full-screen interface:

👉 <a href="https://wilanmonlo-async-csv-api.hf.space" target="_blank"><b>Try the Live API (Swagger UI)</b></a>

### API Testing Workflow:
1. **`POST /api/products/import` (Upload a Catalog):** Click *Try it out*, select your formatted `.csv` file (following this README's guide), and click *Execute*. The server immediately responds with `202 Accepted` and a JSON object containing the process ID (for example, `"import_id": 1`).
2. **`GET /api/products/import/{id}` (Monitor Progress):** Enter the generated ID in this endpoint and click *Execute*. Poll the endpoint to see the status change from `pending` to `processing` in real time, while the processed row count (`processed_rows`) and progress percentage (`progress`) increase in batches of 500 until the status reaches `100% completed`.
