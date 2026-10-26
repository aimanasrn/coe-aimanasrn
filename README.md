# StudyMart

StudyMart is a full-stack shopping and inventory management application for educational products. Customers can browse products, manage a shopping cart, and update their profile. Administrators can manage products, categories, stock, and product images, and view a dashboard.

The repository contains a Laravel REST API and a separate Nuxt web application. Nuxt forwards browser requests through its `/api` routes to Laravel and manages authentication using an HTTP-only session cookie.

## Technology stack

| Component | Technology |
| --- | --- |
| Backend | Laravel 13, PHP, JWT authentication |
| Frontend | Nuxt 3, Vue 3, Nuxt UI, Tailwind CSS |
| Database | PostgreSQL |
| Cache and sessions | Redis |
| Image storage | Local public disk, or S3-compatible Garage storage in Docker |

## Repository structure

```text
src/
  backend/              Laravel API, migrations, seeders, and tests
  frontend/             Nuxt pages, components, and server-side API proxy
docker-compose.yml      Full development stack
compose.storage.yaml    Redis and Garage services included by Compose
```

## Start developing

The commands in these guides use Bash, including Ubuntu/WSL on Windows.

```bash
git clone https://github.com/aimanasrn/coe-aimanasrn.git
cd coe-aimanasrn
```

1. Follow the [backend setup guide](src/backend/README.md) to configure PostgreSQL and Redis, install dependencies, generate keys, and start the API on `http://localhost:8000`.
2. In another terminal, follow the [frontend setup guide](src/frontend/README.md) to install dependencies and start Nuxt on `http://localhost:3000`.
3. Open <http://localhost:3000/register> to create a customer account. For administrator access, follow the backend guide's local administrator setup.

Keep both development servers running. The frontend's API proxy targets `http://127.0.0.1:8000/api` by default.

## Docker development

The Compose stack runs PostgreSQL 17, Redis 7.4, Garage, PHP 8.5, and Node.js 24. It requires Docker Engine and Docker Compose v2 with support for `include` and optional `env_file` entries.

**Fresh-clone prerequisite:** `.env.garage` and `garage.toml` are ignored by Git and have no committed examples. Garage requires these files, and the backend needs matching S3 credentials in `.env.garage`. Obtain the local configuration from the project maintainer or configure Garage before using this stack. The host-based setup guides above use local image storage and do not require Garage.

Once Garage is configured, run from the repository root:

```bash
# First-time setup only; preserve existing environment files.
cp -n .env.example .env
cp -n src/backend/.env.example src/backend/.env
```

Set `POSTGRES_PASSWORD` in the root `.env`. Generate the backend's JWT secret before starting the frontend:

```bash
docker compose build backend
docker compose run --rm --no-deps --entrypoint php backend artisan jwt:secret --no-interaction
docker compose up -d --build
docker compose ps
```

The backend entrypoint installs dependencies, generates `APP_KEY` when missing, runs migrations, and initializes image storage. It does not generate `JWT_SECRET` or seed accounts. Open <http://localhost:3000> after the services are healthy.

```bash
docker compose logs --tail=100 backend frontend
docker compose stop
```

The root `.env` supports `FRONTEND_PORT`, `BACKEND_PORT`, and `POSTGRES_PORT` overrides. Keep local environment files out of Git.

The older [object storage guide](OBJECT_STORAGE.md) describes a previous RustFS setup. The current Compose files use Garage on port `3900`; the RustFS service names, ports, and console instructions in that guide do not apply to this stack.
