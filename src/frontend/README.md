# StudyMart frontend

The Nuxt 3 app (Vue 3, Nuxt UI, Tailwind CSS) provides registration, login, the customer shop and cart, profile management, and the administrator dashboard.

**To set up the project, follow the step-by-step guide in the [root README](../../README.md).** This page is a reference for working on the frontend.

## Main parts

| Path | Contents |
| --- | --- |
| `pages/` | Routes. `admin/` for administrators; `shop/` and `cart.vue` for customers |
| `layouts/` | `admin` and `shop` page layouts |
| `components/` | UI components grouped into `admin/`, `shop/`, `products/`, and `auth/` |
| `composables/` | Data and state helpers such as `useAuth`, `useCart`, and `useProducts` |
| `middleware/auth.global.ts` | Sends guests to login and keeps each role on its own pages |
| `server/routes/api/` | Proxy to Laravel. Stores the login token in an HTTP-only cookie |

## Pages

| Page | Path | Who can open it |
| --- | --- | --- |
| Home | `/` | Everyone |
| Register / log in | `/register`, `/login` | Guests |
| Shop and cart | `/shop`, `/cart` | Customers |
| Profile | `/profile` | Any logged-in user |
| Admin dashboard | `/admin` | Administrators |

## API configuration

No frontend `.env` is needed when Laravel runs on port 8000. The defaults in [`nuxt.config.ts`](nuxt.config.ts):

| Setting | Default | Purpose |
| --- | --- | --- |
| `NUXT_API_BASE` | `http://127.0.0.1:8000/api` | Where the Nuxt server sends API requests |
| `NUXT_PUBLIC_API_BASE` | `/api` | Where the browser sends API requests (always through Nuxt) |

If Laravel uses another port, create `src/frontend/.env` with:

```dotenv
NUXT_API_BASE=http://127.0.0.1:8001/api
```

Include `/api` at the end. Restart Nuxt after changing it. Docker Compose sets `NUXT_API_BASE=http://backend:8000/api` automatically.

## Common commands

Run from `src/frontend`:

| Task | Command |
| --- | --- |
| Install dependencies | `npm ci` |
| Start the dev server | `npm run dev` |
| Use another port | `npm run dev -- --port 3001` |
| Production build and preview | `npm run build`, then `npm run preview` |

The app depends on Nuxt server routes for login and the API proxy, so a static build (`npm run generate`) does not work on its own.

For troubleshooting, see the [root README](../../README.md#troubleshooting).
