# 01 · Arquitectura y principios

Los "por qué" estructurales del proyecto. Cada decisión de arquitectura es un trade-off
(una ventaja pagada con un costo) — no hay decisiones mágicas.

---

## 1. Cliente-servidor: la separación fundamental

```
Navegador (cliente)          Servidor (backend)
┌──────────────────┐   HTTP   ┌──────────────────┐
│ Next.js (Vercel) │ ───────► │ Laravel (API)    │ ──► MySQL
│ pinta la tienda  │ ◄─────── │ devuelve JSON    │
└──────────────────┘  JSON    └──────────────────┘
```

- El **frontend** se ocupa de *presentar*: HTML, CSS, interacción, animaciones.
- El **backend** se ocupa de *datos y reglas*: qué productos existen, precios, stock, permisos.
- Se comunican por **HTTP intercambiando JSON** (el contrato).

**Por qué no todo junto (monolito tipo Laravel Blade o WordPress):**
el equipo se divide naturalmente (A/B/C), el frontend se despliega en Vercel gratis con
CDN global, y cada lado evoluciona sin recompilar al otro. El costo: dos deploys, CORS,
y latencia de red entre ambos — costos que gestionamos en la Fase 0.

**Regla que se deriva:** la lógica de negocio nunca vive en el frontend. El precio se
calcula en el backend; el frontend solo lo *muestra*. Si mañana hay descuentos, se
aplican en Laravel. ¿Por qué? Porque el navegador es territory hostil: cualquiera puede
abrir DevTools y manipular el JavaScript.

## 2. Contract-first: el documento antes que el código

Ver [`api-contracts.md`](../../api-contracts.md). El flujo del equipo:

```
necesidad → se discute → se actualiza el contrato → LUEGO se programa backend y frontend
```

Es la aplicación práctica de **programación por interfaces**: el frontend depende de la
*forma* de la respuesta, no de su implementación. Igual que un enchufe: no necesitas
conocer la central eléctrica para conectar una lámpara.

## 3. 12-Factor App (los factores que ya aplicamos)

Es una metodología (12factor.net) para apps que viven en la nube. Ya usamos tres:

| Factor | En el proyecto |
|---|---|
| **Config en entorno** | Todo lo que cambia entre local/producción vive en `.env` (BD, CORS), nunca en el código |
| **Procesos sin estado** | La API no recuerda nada entre requests; el carrito vive en el navegador del usuario |
| **Paridad dev/prod** | SQLite en dev y MySQL en prod son el mismo esquema vía migrations |

El `.env` **jamás se commitea** (está en `.gitignore`): contiene secretos y cada
máquina/servidor tiene el suyo. Se versiona `.env.example` con los nombres y valores vacíos.

## 4. Convención sobre configuración

Laravel y Next.js funcionan "por defecto" si sigues sus convenciones:

- Laravel: el modelo `Product` ← tabla `products` (inglés, plural, snake_case). Migración
  en `database/migrations/`. Seeder en `database/seeders/`.
- Next.js: `src/app/producto/[slug]/page.tsx` **es** la ruta `/producto/mi-slug`. No
  registras rutas en ningún archivo.

Ventaja: menos código de "pegamento" y cualquier dev de Laravel/Next abre el proyecto y se
orienta. Desventaja (a asumir): hay que aprender las convenciones — por eso existen estos apuntes.

## 5. KISS, YAGNI, DRY — con ejemplos reales del proyecto

- **KISS** (Keep It Simple): el carrito es un array en localStorage, no una tabla con
  sesiones y APIs. Lo simple que funciona hoy gana a lo brillante que funciona en marzo.
- **YAGNI** (You Aren't Gonna Need It): *no* hicimos login de clientes, reserva de stock,
  pasarela de pago ni panel de métricas. No porque sean malos, sino porque **aún no se
  necesitan**. El código que no existe no tiene bugs, no se mantiene y no estorba.
- **DRY** (Don't Repeat Yourself): los **scopes** de Eloquent
  (`Product::published()`) existen para que la condición `status = 'published'` esté
  escrita una sola vez. Si mañana "publicado" implica además `published_at <= now`,
  se cambia en un lugar.

⚠️ DRY mal entendido: duplicar *dos líneas* es peor que un buen nombre. DRY aplica a
*conocimiento*, no a texto idéntico por casualidad.

## 6. Stateless vs stateful (y dónde está el estado de la app)

| Estado | Dónde vive | Tecnología |
|---|---|---|
| Catálogo, stock | Servidor | MySQL |
| Sesión del admin | Servidor (cookie firmada) | Laravel session |
| Carrito del usuario | Cliente | localStorage + Zustand |
| UI (modal abierto, filtros) | Cliente | estado de React |

REST es **stateless**: cada request lleva todo lo necesario para entenderse (o una cookie
de sesión para el admin). No hay "conexión persistente" entre navegador y API.

## 7. ¿Esto es microservicios? No — y está bien

Este proyecto es un **monolito modular** (un solo Laravel con capas internas) + un
frontend desacoplado. Microservicios serían varios backends independientes con su propia
BD. Para 3 personas y una tienda: los microservicios añadirían complejidad operativa
(desarrollo distribuido, consistencia de datos, deploys múltiples) sin ningún beneficio.
Saber *cuándo no* usar una arquitectura de moda también es arquitectura.

## 8. MVC: dónde encaja cada archivo

Laravel es MVC (Modelo-Vista-Controlador):

```
GET /api/v1/products
   → Ruta (routes/api.php)
   → Middleware (CORS, auth)
   → Controlador (decide QUÉ hacer)     ← Fase 1
   → Modelo (sabe CÓMO pedir datos)     ← ya escrito
   → Respuesta JSON (la "vista" de una API)
```

La tentación junior: poner consultas en el controlador o en la vista. Regla del
proyecto: **el controlador orquesta; el modelo sabe; la vista/nombre muestra**.

---

## ✅ Autoevaluación

1. ¿Por qué el precio con descuento se calcularía en el backend y no en el frontend?
2. ¿Qué problema del trabajo en equipo resuelve el contrato de API que el código solo no puede?
3. Nombra los tres factores 12-factor que ya aplicamos y dónde se ven.
4. ¿Es este proyecto stateless? ¿Dónde vive el carrito y por qué ahí?
5. Explica a un compañero por qué NO usamos microservicios (tres razones).
