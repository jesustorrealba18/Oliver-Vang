# 04 · Next.js esencial

El React del mercado: App Router, renderizado del servidor y foco en rendimiento.
Regla del proyecto: ante dudas, mira las guías oficiales incluidas en
`frontend/node_modules/next/dist/docs/` (Next 16 tiene cambios importantes).

---

## 1. React en 60 segundos (modelo mental)

- La UI es una función del **estado**: `UI = f(estado)`. No "escribes" el HTML dinámico:
  describes cómo se ve para cada estado y React lo actualiza.
- **Componentes** = piezas reutilizables (`<ApiStatus />`). **Props** = parámetros de
  entrada (de padre a hijo). **Estado** = memoria local que al cambiar re-renderiza.
- **Hooks**: `useState` (memoria), `useEffect` (sincronizar con el exterior: red, timers).

`🔎 Dónde verlo:` `frontend/src/components/api-status.tsx` usa useState + useEffect + motion.

## 2. Por qué Next.js y no React "puro" (Vite)

E-commerce = SEO = Google debe leer tus productos. React puro renderiza **en el
navegador** (Client-Side Rendering): Google ejecuta JS, pero es más lento y menos fiable.
Next renderiza **en el servidor** el HTML inicial (SSR/SSG) → el HTML ya llega con el
hoodie y su precio escritos. Además: enrutamiento por archivos, optimización de imágenes,
fuentes y bundling sin configurar webpack.

## 3. App Router: los archivos SON las rutas

```
src/app/
  layout.tsx        → plantilla raíz (html/body, fuentes, metadata) — envuelve TODO
  page.tsx          → la ruta /
  producto/[slug]/page.tsx  → ruta dinámica /producto/camiseta-boxy-logo (Fase 1)
```

No hay archivo de rutas. Cada carpeta = segmento de URL. `layout.tsx` persiste entre
navegaciones (no se recarga el header/footer).

## 4. EL concepto clave: Server vs Client Components

La confusión nº1 de Next. Dos mundos en el mismo árbol:

| | Server Component (defecto) | Client Component (`"use client"`) |
|---|---|---|
| Dónde corre | Solo servidor | Servidor (1ª vez) + navegador |
| Puede | async/await, BD, secretos | estado, eventos, useEffect, animación |
| No puede | useState, onClick, useEffect | acceder a secretos del servidor |
| En el proyecto | `src/app/page.tsx` | `src/components/api-status.tsx` |

**Cómo pensarlo:** el servidor manda el HTML "dormido"; un Client Component es una
"isla" que despierta en el navegador (hydration) para ser interactiva. Regla del
proyecto: **"use client" lo más abajo posible del árbol** — solo donde hay
interactividad. Si pones `"use client"` en el layout, toda la web pierde SSR.

Por qué `ApiStatus` es cliente: necesita `useEffect` (hacer ping cuando se monta en el
navegador) y estado (`checking/online/offline`). Por qué `page.tsx` es servidor: solo
pinta contenido estático.

## 5. Fetching de datos (comportamiento Next 16)

- `fetch` en Server Components es **sin caché por defecto**: cada request pide datos frescos.
- Para cachear: directiva `use cache` (lo veremos en Fase 1 para el catálogo, que cambia poco).
- Nuestro cliente `src/lib/api.ts` fuerza `cache: "no-store"` — intención explícita:
  nada de servir un producto agotado como disponible.
- Listado + Suspense/streaming para que la página no espere al dato más lento (Fase 1).

## 6. Variables de entorno: el prefijo NEXT_PUBLIC_

```
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api/v1   (frontend/.env.local)
```

**Regla:** `NEXT_PUBLIC_` = se "hornea" dentro del JS que viaja al navegador (visible para
cualquiera). Sin el prefijo = solo servidor. La URL de la API es pública de todos modos
(así funciona CORS) — pero si algún día añades un token privado, NUNCA lleva el prefijo.

## 7. Tipografía y metadata (SEO ya incorporado)

- `next/font`: carga la fuente en build y la self-hostea → sin parpadeo ni petición a
  Google en runtime. `🔎 frontend/src/app/layout.tsx` (Geist; el equipo A la cambia por
  la de marca).
- Metadata API: `export const metadata = { title: { template: "%s | VANG" } }` — el
  template hace que una ficha de producto se titule "Hoodie Graffiti | VANG"
  automáticamente. En Fase 1 cada página exporta la suya (`generateMetadata` para datos dinámicos).

## 8. Tailwind CSS: utilidades, no CSS custom

```html
<h1 class="mt-5 text-8xl font-black tracking-tighter">
```

Cada clase es una utilidad atómica (`mt-5` = margin-top). Filosofía: **no escribas CSS,
compón clases**. Ventajas: sin archivo CSS por componente, sin nombres inventados
("card-wrapper-2"), el diseño vive junto al markup y el equipo habla el mismo idioma
visual (design tokens en `globals.css`). Coste: HTML verboso (se acepta).

Tailwind v4 se configura **con CSS** (`@import "tailwindcss"` + variables en
`globals.css`), ya sin `tailwind.config.js`.

## 9. Zustand y Framer Motion (las dos librerías de la stack)

- **Zustand** (Fase 2): estado global sin la parafernalia de Redux. Un "store" = función
  `create()` con datos y acciones. Ahí vivirá el carrito: `items`, `add()`, `remove()`.
  Se eligió por tamaño (1kb) y API mínima.
- **Framer Motion** (Fase 1–2): animaciones declarativas — describes el estado final y
  la transición, no keyframes. `🔎 api-status.tsx`: `animate={{ opacity: [1, 0.3, 1] }}`
  hace el pulso del indicador. En la web de moda urbana, las transiciones son marca.

## 10. TypeScript: por qué el mercado lo paga

Types = tests baratos que documentan. `src/lib/api.ts` define la forma EXACTA del
contrato (`ProductDetail`, `PaginationMeta`...): si el backend cambia un campo, el
front falla en compilación, no en producción a las 3am. Además el editor autocompleta
`product.variants[0].stock` — no memorizas la API, te la muestran.

---

## ✅ Autoevaluación

1. ¿Por qué una tienda online necesita SSR y con SSR ¿qué gana Google?
2. Necesitas un botón con onClick: ¿server o client component? ¿Dónde colocas la marca `"use client"` y por qué lo más bajo posible?
3. ¿Qué hace `NEXT_PUBLIC_` y qué nunca debería llevar ese prefijo?
4. ¿Por qué `ApiStatus` no puede ser un Server Component?
5. Mira `api.ts`: ¿qué garantiza `cache: "no-store"` y qué problema real evita en una tienda?
6. Explica `title.template` y qué título genera la ficha del hoodie.
