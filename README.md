# VANG — Tienda de ropa urbana

Web de ropa underwear/urbana. **Fase 1: catálogo sin pasarela de pago** — la venta se
concreta por WhatsApp. Equipo de 3: **A** (Frontend/Estilo) · **B** (Backend/API) · **C** (Fullstack/Admin).

## Stack

- **frontend/** — Next.js (App Router) + TypeScript + Tailwind CSS + Framer Motion → Vercel
- **backend/** — Laravel 12 (API REST JSON) + Filament (panel admin) → host PHP/MySQL o VPS
- **BD** — MySQL en producción · SQLite en desarrollo local (cero configuración)

## Documentación clave (leer antes de programar)

| Documento | Qué contiene |
|---|---|
| [`plan-tienda-urbana.md`](plan-tienda-urbana.md) | Plan completo: fases, roles, riesgos |
| [`schema.sql`](schema.sql) | Referencia del esquema de BD (las migrations producen esto) |
| [`api-contracts.md`](api-contracts.md) | Contrato de la API. **Nunca cambiar en silencio** |
| [`docs/estudio/`](docs/estudio/README.md) | Apuntes de estudio: por qué de cada decisión + conceptos por fase |

## Levantar el proyecto (desarrollo local)

### Backend (puerto 8000)

```bash
cd backend
composer install
cp .env.example .env          # luego: php artisan key:generate
php artisan migrate --seed    # crea tablas + datos de ejemplo
php artisan serve             # API en http://localhost:8000
```

Probar: `curl http://localhost:8000/api/v1/ping` → `{"data":{"message":"pong",...}}`

**Panel admin (Filament):** `http://localhost:8000/admin`
- admin@vang.test / password (rol admin)
- editor@vang.test / password (rol editor)

### Frontend (puerto 3000)

```bash
cd frontend
npm install
cp .env.local.example .env.local   # apunta a la API local
npm run dev                        # http://localhost:3000
```

## Base de datos: SQLite (dev) → MySQL (producción)

En desarrollo usamos **SQLite** (ya configurada): nadie necesita instalar MySQL local.
Las migrations son idénticas en ambos motores.

Para pasar a MySQL en producción: crear la BD y cambiar el bloque `DB_*` del `.env`:

```sql
CREATE DATABASE vang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vang
DB_USERNAME=vang
DB_PASSWORD=•••
```

Luego `php artisan migrate --seed`.

## Reglas del proyecto

1. El **stock vive en `variants`** (color + talla), nunca en `products`
2. Precios en `DECIMAL(10,2)`, nunca FLOAT. En el JSON viajan como string
3. Respuestas de la API siempre envueltas en `data`; errores con el formato del contrato
4. Cambios en el contrato de la API: **se avisa al equipo primero** y se actualiza `api-contracts.md`
5. Commits en español o inglés pero descriptivos; nada a `main` sin revisión del dueño de la fase

## Estado del proyecto

- [x] **Fase 0 — Fundamentos**: repo, Laravel + Filament, 7 migrations, modelos, seeders, ping, CORS, Next.js base
- [ ] **Fase 1 — Catálogo público**: home, grid, ficha de producto + endpoints del contrato
- [ ] **Fase 2 — Venta por WhatsApp**: carrito + generador del mensaje
- [ ] **Fase 3 — Admin completo**: CRUD Filament, roles, settings
- [ ] **Fase 4 — Pulido**: SEO, analytics, performance, deploy
