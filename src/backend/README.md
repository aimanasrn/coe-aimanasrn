# StudyMart backend

The Laravel 13 backend provides JWT authentication, customer profiles and carts, product browsing, and administrator APIs for the catalog and dashboard. API routes are defined in [`routes/api.php`](routes/api.php) and served under `/api`.

## Requirements

- PHP 8.5 to match the Docker development environment, with Composer 2 and the extensions required by `composer.lock`, including `pdo_pgsql`, `mbstring`, and `fileinfo`.
- PostgreSQL (the Docker stack uses version 17).
- Redis (the Docker stack uses version 7.4) for caching, sessions, and JWT invalidation.

These instructions run Laravel directly on your host. Use Bash or Ubuntu/WSL, and keep PostgreSQL and Redis running. Node.js is only needed here if you work on Laravel's own Vite assets; the main web application runs separately in `src/frontend`.

## First-time setup

### 1. Create a PostgreSQL database

Connect as a PostgreSQL administrator, for example with `sudo -u postgres psql` on Ubuntu, and run the following SQL once. Choose your own local password and use the same value in Laravel's environment file.

```sql
CREATE USER studymart WITH PASSWORD 'replace_with_your_local_password';
CREATE DATABASE studymart OWNER studymart;
```

Use PostgreSQL for this project. Although `.env.example` defaults to SQLite, the product migration contains `ALTER TABLE ... ADD CONSTRAINT` statements that do not work with SQLite.

### 2. Install dependencies and configure Laravel

From the repository root:

```bash
cd src/backend
cp -n .env.example .env
composer install
```

Edit **`src/backend/.env`** and replace the existing values with your local connection settings:

```dotenv
APP_NAME=StudyMart
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=studymart
DB_USERNAME=studymart
DB_PASSWORD=replace_with_your_local_password

SESSION_DRIVER=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

FILESYSTEM_DISK=local
PRODUCT_IMAGES_DISK=public
```

Keep the other example settings. If Redis requires a password, set `REDIS_PASSWORD` accordingly. Local public storage works without S3 credentials or Garage.

### 3. Initialize the application

Run from `src/backend`:

```bash
php artisan key:generate --no-interaction
php artisan jwt:secret --no-interaction
php artisan config:clear --no-interaction
php artisan migrate --no-interaction
php artisan storage:link --no-interaction
```

Generate the application and JWT keys once when setting up a new `.env`; preserve them on later starts. The storage link exposes locally stored images through `/storage`.

Optionally add a sample Books category and Mathematics Workbook product:

```bash
php artisan db:seed --class=CatalogSeeder --no-interaction
```

### 4. Start the API

```bash
php artisan serve --host=127.0.0.1 --port=8000 --no-interaction
```

The API is available at `http://127.0.0.1:8000/api`. In another terminal, check Laravel's health endpoint:

```bash
curl --fail http://127.0.0.1:8000/up
```

Start the [frontend](../frontend/README.md) separately. The `/api/products` and `/api/categories` endpoints require an authenticated administrator; use `/up` to check that Laravel is running.

## Local administrator setup

Registration at <http://localhost:3000/register> creates a customer account. There is no automatically seeded administrator login.

For local development, register your account first, then open Tinker from `src/backend`:

```bash
php artisan tinker
```

Run the following PHP, replacing the email with the account you registered:

```php
$user = App\Models\User::where('email', 'your-email@example.com')->firstOrFail();
$user->role = App\UserRole::Admin;
$user->save();
```

Exit Tinker, log out, and log in again before opening <http://localhost:3000/admin>.

## Daily development and troubleshooting

Run `php artisan serve --host=127.0.0.1 --port=8000 --no-interaction` from this directory to start again. After pulling new migrations, run `php artisan migrate --no-interaction`. After editing `.env`, run `php artisan config:clear --no-interaction` and restart the server.

| Symptom | Check |
| --- | --- |
| Database connection refused | PostgreSQL is running and `DB_*` matches your local database. |
| SQLite migration syntax error | Set `DB_CONNECTION=pgsql` and clear cached configuration. |
| Redis connection refused | Redis is running and `REDIS_*` matches the server. |
| Missing application key or JWT secret | Run the corresponding key generation command during first-time setup. |
| Administrator page returns access denied | Promote your registered local account, then log in again. |
| Images fail to load | Run `storage:link` and check `APP_URL` and `PRODUCT_IMAGES_DISK`. |
| Port 8000 is occupied | Choose another `serve --port`, update `APP_URL`, and set the frontend's `NUXT_API_BASE` to match. |

Feature tests rebuild database tables, so configure a separate PostgreSQL test database before running them. The committed `phpunit.xml` defaults to SQLite and needs PostgreSQL overrides for migrations to work. Do not point tests at your development database.

For Docker startup and the current Garage configuration prerequisite, see the [root README](../../README.md#docker-development). The [older object storage guide](../../OBJECT_STORAGE.md) documents RustFS and does not match the current Garage services.
