# CraftMarket — Vue.js 3 Frontend

The single-page application for **CraftMarket**, a local handmade products marketplace. It consumes the Laravel REST API in `../backend` and falls back to sample data when the API is unavailable.

## Stack

- Vue.js 3 (`<script setup>` SFCs)
- Vite 8
- Vue Router 4
- Axios
- DOMPurify (sanitizes CMS-provided HTML)
- Custom CSS design system in `src/assets/css/`

## Setup

```powershell
cd frontend
npm install
npm run dev
```

The Vite dev server normally starts at `http://localhost:5173`.

## API configuration

The API base URL defaults to `http://127.0.0.1:8000/api`. Override it with an environment file if needed:

```env
# frontend/.env.local
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

## Scripts

| Command | Description |
|---|---|
| `npm run dev` | Start the Vite development server |
| `npm run build` | Build the production bundle into `dist/` |
| `npm run preview` | Preview the production build locally |

## Source layout

```text
src/
├── assets/css/      # Design system and page styles
├── components/      # Reusable UI (Navbar, product cards, dashboards tables…)
├── composables/     # Shared composition functions (useAuth)
├── data/            # Reactive client state (auth, cart, wishlist)
├── router/          # Routes and authentication/role guards
├── services/        # Axios services for the Laravel API
├── views/           # Route-level pages
├── App.vue
└── main.js
```

## Notes

- Auth tokens are stored in `sessionStorage` (sessions do not persist across browser restarts by design).
- Route guards protect customer, seller, and admin dashboards.
- When the backend is offline, the home page and product catalog use built-in sample data.
