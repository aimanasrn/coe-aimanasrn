# StudyMart backend

The Laravel 13 API handles authentication, customer profiles and carts, product browsing, and the administrator catalog and dashboard. Routes are in [`routes/api.php`](routes/api.php) and are served under `/api`.

**To set up the project, follow the step-by-step guide in the [root README](../../README.md).** This page is a reference for working on the backend.

## Main parts

| Path | Contents |
| --- | --- |
| `app/Http/Controllers/Api/` | Auth, profile photo, shop, cart, product, category, and dashboard endpoints |
| `app/Http/Middleware/EnsureTokenIsCurrent.php` | Rejects tokens issued before the user's `token_version` changed |
| `app/Providers/AppServiceProvider.php` | `shop` (customers) and `manage-catalog` (admins) permission gates |
| `app/CatalogCache.php` | Caches catalog queries in Redis for 60 seconds; cleared when products or categories change |
| `app/Console/Commands/InitializeLocalStorage.php` | Creates the Garage bucket in Docker |
| `database/seeders/StudyMartCatalogSeeder.php` | All categories and StudyMart products, with automatic SKUs |
| `tests/Feature/` | Feature tests |

## Environment settings

`.env.example` is the template for `.env`. The settings you are most likely to change:

| Setting | Without Docker | Docker (set by Compose) |
| --- | --- | --- |
| `DB_CONNECTION` | `pgsql` (SQLite is not supported) | `pgsql` |
| `DB_HOST`, `DB_PORT` | `127.0.0.1`, `5432` | `postgres`, `5432` |
| `REDIS_HOST` | `127.0.0.1` | `redis` |
| `PRODUCT_IMAGES_DISK` | `public` (local disk) | `s3` (Garage) |
| `AWS_*` | Not needed | From the root `.env.garage` |
| `APP_KEY`, `JWT_SECRET` | Generated once during setup | Generated once during setup |

After editing `.env`, run `php artisan config:clear` and restart the server.

## Common commands

Run from `src/backend`. With Docker, put `docker compose exec backend` in front of each `php artisan` command.

| Task | Command |
| --- | --- |
| Start the API | `php artisan serve --host=127.0.0.1 --port=8000` |
| Check the API is running | `curl --fail http://127.0.0.1:8000/up` |
| Apply new migrations | `php artisan migrate` |
| Add the catalog and test customer | `php artisan db:seed` |
| List API routes | `php artisan route:list --path=api` |
| Run tests | See [Run the backend tests](../../README.md#run-the-backend-tests) |

The `/api/products` and `/api/categories` endpoints require an administrator login. Use `/up` to check that Laravel is running.

For troubleshooting, see the [root README](../../README.md#troubleshooting).
