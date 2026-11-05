# StudyMart

StudyMart is a full-stack shopping and inventory management application for educational products. Customers browse products, manage a shopping cart, and update their profile. Administrators manage products, categories, stock, and product images, and view a dashboard.

The repository contains a Laravel REST API and a separate Nuxt web application. The browser only talks to Nuxt. Nuxt forwards `/api` requests to Laravel and keeps the login token in an HTTP-only cookie.

| Component | Technology |
| --- | --- |
| Backend | Laravel 13, PHP 8.5, JWT authentication |
| Frontend | Nuxt 3, Vue 3, Nuxt UI, Tailwind CSS |
| Database | PostgreSQL 17 |
| Cache and sessions | Redis 7.4 |
| Image storage | Garage (S3-compatible) in Docker, or the local disk without Docker |

```text
src/backend/            Laravel API, migrations, seeders, and tests
src/frontend/           Nuxt pages, components, and server-side API proxy
docker-compose.yml      PostgreSQL, backend, and frontend services
compose.storage.yaml    Redis and Garage services (included by docker-compose.yml)
```

## Choose a setup path

| | Option A: Docker (recommended) | Option B: Without Docker |
| --- | --- | --- |
| You install | Docker Desktop or Docker Engine with Compose v2 | PHP 8.5, Composer 2, PostgreSQL, Redis, Node.js 24 |
| Terminals needed | 1 | 2 (backend and frontend) |
| Image storage | Garage | Local disk |
| Approximate time | 10 minutes, mostly downloads | 20–30 minutes |

Every command in this guide is for a **Bash terminal** (Linux, macOS, or Ubuntu/WSL on Windows). Run each block from the **repository root** unless the step says otherwise.

Both options start by cloning the repository:

```bash
git clone https://github.com/aimanasrn/coe-aimanasrn.git
cd coe-aimanasrn
```

---

## Option A: Docker

### A1. Check that Docker works

```bash
docker --version
docker compose version
```

Both commands should print a version. `docker compose version` must report **v2.24 or newer**.

On Windows, start Docker Desktop and enable WSL integration for your Ubuntu distribution (Settings → Resources → WSL integration).

### A2. Create the configuration files

These four files hold local settings and secrets. Git ignores them; each one is copied from a committed example.

```bash
cp -n .env.example .env
cp -n .env.garage.example .env.garage
cp -n garage.toml.example garage.toml
cp -n src/backend/.env.example src/backend/.env
```

`cp -n` never overwrites a file that already exists, so this step is safe to repeat.

### A3. Generate the storage secrets

Garage needs three random values. Run this block **once** to write them into `garage.toml` and `.env.garage`:

```bash
sed -i "s/^rpc_secret = .*/rpc_secret = \"$(openssl rand -hex 32)\"/" garage.toml
sed -i "s/^GARAGE_DEFAULT_ACCESS_KEY=.*/GARAGE_DEFAULT_ACCESS_KEY=GK$(openssl rand -hex 16)/" .env.garage
sed -i "s/^GARAGE_DEFAULT_SECRET_KEY=.*/GARAGE_DEFAULT_SECRET_KEY=$(openssl rand -hex 32)/" .env.garage
```

Check the result. Neither command should print `replace_with`:

```bash
grep rpc_secret garage.toml
grep GARAGE_DEFAULT .env.garage
```

Do not run the block again after Garage has started: new keys will not match the stored data. To start over, see [Reset everything](#reset-everything).

### A4. Set the database password

Open the root `.env` and change `POSTGRES_PASSWORD` to a password of your choice. The default value works for local development. You do not need to edit `src/backend/.env`; Docker Compose supplies the database, Redis, and storage settings.

If ports 3000, 8000, or 5432 are already used on your computer, change `FRONTEND_PORT`, `BACKEND_PORT`, or `POSTGRES_PORT` in the same file.

### A5. Build the images and generate the login secret

```bash
docker compose build
docker compose run --rm --no-deps --entrypoint php backend artisan jwt:secret --no-interaction
```

The second command writes `JWT_SECRET` into `src/backend/.env`. Laravel needs it to sign login tokens. Run it only once.

### A6. Start the stack

```bash
docker compose up -d
```

The first start takes a few minutes. The backend installs PHP packages, creates `APP_KEY`, runs the database migrations, and creates the storage bucket. Watch progress with:

```bash
docker compose ps
```

Wait until every service shows `healthy`. If one stays `starting` or shows `unhealthy`, read its log, for example `docker compose logs --tail=100 backend`.

### A7. Check that it works

```bash
curl --fail http://localhost:8000/up
```

This should return an HTML page without an error. Then open <http://localhost:3000> in your browser.

Continue with [Create your accounts](#create-your-accounts). In the Docker setup, run every `php artisan` command through the backend container:

```bash
docker compose exec backend php artisan <command>
```

### Daily Docker commands

| Task | Command |
| --- | --- |
| Start | `docker compose up -d` |
| Stop | `docker compose stop` |
| View logs | `docker compose logs -f backend frontend` |
| Apply new migrations after pulling | `docker compose exec backend php artisan migrate` |
| Rebuild after dependency changes | `docker compose up -d --build` |

### Reset everything

This deletes the database, Redis data, and uploaded images:

```bash
docker compose down -v
rm .env.garage garage.toml
```

Then repeat from [A2](#a2-create-the-configuration-files).

---

## Option B: Without Docker

### B1. Install the tools

Install these and make sure each command prints a version:

| Tool | Check | Notes |
| --- | --- | --- |
| PHP 8.5 | `php -v` | Needs the `pdo_pgsql`, `mbstring`, `intl`, `bcmath`, and `zip` extensions. Check with `php -m`. |
| Composer 2 | `composer --version` | |
| PostgreSQL | `psql --version` | The server must be running. |
| Redis | `redis-cli ping` | Should reply `PONG`. |
| Node.js 24 | `node -v` | npm is included. |

You can also run only PostgreSQL and Redis in Docker and everything else on your computer:

```bash
cp -n .env.example .env
cp -n .env.garage.example .env.garage
cp -n garage.toml.example garage.toml
docker compose up -d postgres redis
```

Compose reads the Garage files even when Garage is not started, so they must exist. You do not need to fill them in. The database user and password are the `POSTGRES_*` values in the root `.env`, and the database already exists. Skip B2 and use those values in B3.

### B2. Create the database

Open PostgreSQL as an administrator. On Ubuntu:

```bash
sudo -u postgres psql
```

Run this SQL. Choose your own password and remember it for the next step:

```sql
CREATE USER studymart WITH PASSWORD 'choose_a_password';
CREATE DATABASE studymart OWNER studymart;
\q
```

Use PostgreSQL. SQLite does not work: one migration uses SQL that SQLite does not support.

### B3. Configure the backend

```bash
cd src/backend
cp -n .env.example .env
composer install
```

Open `src/backend/.env`. Find the `DB_` lines, which point to SQLite, and replace them with:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=studymart
DB_USERNAME=studymart
DB_PASSWORD=choose_a_password
```

Leave the other settings as they are. The defaults already use Redis on `127.0.0.1:6379` and store images on the local disk.

### B4. Prepare the backend

Still in `src/backend`:

```bash
php artisan key:generate --no-interaction
php artisan jwt:secret --no-interaction
php artisan migrate --no-interaction
php artisan storage:link --no-interaction
```

Run these once. Running the two key commands again would invalidate existing logins.

### B5. Start the backend (terminal 1)

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Leave this terminal open. In a new terminal, check it:

```bash
curl --fail http://127.0.0.1:8000/up
```

### B6. Start the frontend (terminal 2)

From the repository root:

```bash
cd src/frontend
npm ci
npm run dev
```

Leave this terminal open, then open <http://localhost:3000>. The frontend expects the backend at `http://127.0.0.1:8000/api`. To use another address, see the [frontend README](src/frontend/README.md#api-configuration).

Continue with [Create your accounts](#create-your-accounts). Run `php artisan` commands from `src/backend`.

### Daily commands without Docker

1. Start PostgreSQL and Redis.
2. Terminal 1: `cd src/backend && php artisan serve --host=127.0.0.1 --port=8000`
3. Terminal 2: `cd src/frontend && npm run dev`

After pulling new changes, run `php artisan migrate` in `src/backend` and `npm ci` in `src/frontend`.

---

## Create your accounts

Both setup options end here.

### 1. Register a customer

Open <http://localhost:3000/register> and create an account. New accounts are customers and can use the shop and cart.

### 2. Make an administrator

There is no default administrator. Register a second account (for example `admin@example.com`), then promote it. Use the `php artisan` form for your setup option:

```bash
# Option A (Docker)
docker compose exec backend php artisan tinker --execute 'App\Models\User::where("email", "admin@example.com")->firstOrFail()->forceFill(["role" => "admin"])->save();'

# Option B (without Docker), from src/backend
php artisan tinker --execute 'App\Models\User::where("email", "admin@example.com")->firstOrFail()->forceFill(["role" => "admin"])->save();'
```

Log out, log in again as that account, and open <http://localhost:3000/admin>.

Administrators and customers see different parts of the app. An administrator cannot use the shop, and a customer cannot open the admin pages.

### 3. Add the StudyMart catalog (optional)

```bash
php artisan db:seed --no-interaction
```

This adds 11 categories and 53 StudyMart products with prices, stock levels, and automatic SKUs. It also creates a `test@example.com` customer account (password: `password`). With Docker, put `docker compose exec backend` in front of the command.

Running it again is safe. It does not create duplicates or overwrite prices, stock, or other changes made in the admin pages.

## Troubleshooting

| Problem | Fix |
| --- | --- |
| `Set POSTGRES_PASSWORD in the root .env` | Run step A2 and set the password in A4. |
| `env file .env.garage not found` | Run steps A2 and A3. |
| Garage stays `unhealthy` | Check `docker compose logs garage`. `rpc_secret` must be exactly 64 hex characters (step A3). |
| Backend stays `starting` | First start can take several minutes. Check `docker compose logs -f backend`. |
| Login fails with a server error | `JWT_SECRET` is missing from `src/backend/.env`. Run step A5 or B4. |
| `could not find driver` or SQLite syntax errors | `DB_CONNECTION` is not `pgsql`. Fix `src/backend/.env` (B3), then run `php artisan config:clear`. |
| Database or Redis connection refused | The service is not running, or the `DB_*` and `REDIS_*` values in `src/backend/.env` are wrong. |
| Frontend shows connection errors | The backend is not running, or its address does not match `NUXT_API_BASE`. |
| Admin page redirects to the profile | The account is a customer. Promote it, then log out and log in again. |
| Product images do not load (Option B) | Run `php artisan storage:link` in `src/backend`. |
| `port is already allocated` or `address already in use` | Another program uses that port, often a PostgreSQL server installed earlier. Change `POSTGRES_PORT`, `BACKEND_PORT`, or `FRONTEND_PORT` in the root `.env` (for example `POSTGRES_PORT=5433`), then run `docker compose up -d` again. |

## Run the backend tests

The tests delete and recreate tables. Never point them at your development database. Create a separate database first (in `psql`, as in B2):

```sql
CREATE DATABASE studymart_test OWNER studymart;
```

Then, from `src/backend`:

```bash
DB_CONNECTION=pgsql DB_DATABASE=studymart_test php artisan test
```

## How image storage works

Laravel stores each uploaded product image on its image disk and saves the file path in the product's `images` column. The browser loads images through Laravel URLs such as `/api/products/12/images/0`, so it never needs storage credentials.

- **Docker:** images go to the `studymart` bucket in Garage (`PRODUCT_IMAGES_DISK=s3`). Garage's S3 endpoint is `http://localhost:3900` from your computer and `http://garage:3900` inside Docker. On start, the backend creates the bucket and copies in any images that are already on the local disk.
- **Without Docker:** images go to `src/backend/storage/app/public` (`PRODUCT_IMAGES_DISK=public`).

More detail is in the [backend README](src/backend/README.md) and the [frontend README](src/frontend/README.md).
