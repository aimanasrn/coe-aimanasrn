# StudyMart frontend

The Nuxt 3 frontend uses Vue 3, Nuxt UI, and Tailwind CSS. It includes customer registration, login, product browsing, a shopping cart, profile management, and an administrator dashboard for products and categories.

## Requirements

- Node.js 24 and npm, matching the frontend Docker image.
- A running [StudyMart backend](../backend/README.md), normally at `http://127.0.0.1:8000`.

The commands below use Bash or Ubuntu/WSL.

## First-time setup

From the repository root:

```bash
cd src/frontend
npm ci
```

`npm ci` installs the dependency versions recorded in `package-lock.json`. Its postinstall step prepares Nuxt automatically.

## API configuration

No frontend `.env` is required when Laravel runs on port `8000`. The defaults in [`nuxt.config.ts`](nuxt.config.ts) are:

| Setting | Default | Purpose |
| --- | --- | --- |
| `NUXT_API_BASE` | `http://127.0.0.1:8000/api` | Laravel URL reached by the Nuxt server. |
| `NUXT_PUBLIC_API_BASE` | `/api` | Same-origin API routes used by the browser. |

If the backend uses another port, create `src/frontend/.env` with:

```dotenv
NUXT_API_BASE=http://127.0.0.1:8001/api
NUXT_PUBLIC_API_BASE=/api
```

Include `/api` in the backend URL. Keep the public URL as `/api` so requests go through Nuxt's authentication and API proxy routes. Restart Nuxt after changing environment variables.

In Docker, Compose sets `NUXT_API_BASE=http://backend:8000/api`, using the backend service's internal address.

## Start the development server

Run from `src/frontend`:

```bash
npm run dev
```

Open <http://localhost:3000>. Keep the backend running in a separate terminal for login, catalog, cart, and profile requests.

| Page | Path |
| --- | --- |
| Home | `/` |
| Register / log in | `/register`, `/login` |
| Customer shop | `/shop` |
| Shopping cart | `/cart` |
| Profile | `/profile` |
| Administrator dashboard | `/admin` |

Register to create a customer account. Protected pages redirect guests to login. Administrator pages require an admin account; see the [backend administrator setup](../backend/README.md#local-administrator-setup).

To use another frontend port:

```bash
npm run dev -- --port 3001
```

## Build and preview

```bash
npm run build
npm run preview
```

The backend must also be running when you preview the built application. This app uses Nuxt server routes for the API proxy and authentication, so use the server build for the complete application. Although `npm run generate` is available, a static-only host does not provide those server routes.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Login or API requests fail with a connection error | Start Laravel and check `NUXT_API_BASE`, including the `/api` suffix. |
| Administrator access is denied | Use an admin account; a newly registered account has the customer role. |
| Port 3000 is occupied | Use `npm run dev -- --port 3001`. |
| Nuxt dependencies are missing after pulling changes | Stop the frontend, run `npm ci`, and start it again. |

For the full Docker stack, see the [root README](../../README.md#docker-development).
