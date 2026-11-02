# 07 · Decisiones de la Fase 0 — el porqué de cada una

Cada decisión técnica fue una elección entre alternativas. Aquí están todas, con el
trade-off a la vista. **Este documento es material de entrevista**: "¿por qué hiciste X
así?" se responde con el formato Decisión → Alternativas → Por qué.

---

## Stack y estructura

### D1. Un solo repo (monorepo) para frontend y backend
- **Alternativas:** dos repos separados.
- **Por qué:** con 3 personas, los cambios suelen tocar ambos a la vez (el contrato). Un
  PR que actualiza endpoint + cliente TS juntos = integración atómica. Dos repos exigen
  sincronizar versiones a mano. Si el equipo creciera a 10+, separar sería razonable.

### D2. Laravel 12 como backend
- **Alternativas:** PHP puro (Slim), Symfony, o que Next.js fuera fullstack (API routes).
- **Por qué:** migraciones, Eloquent y auth resueltos = el equipo se concentra en el
  producto. Symfony es igual de válido pero con más configuración inicial. Next fullstack
  descartado porque el requisito era aprender PHP profesional y desacoplar: la API podría
  servir mañana a una app móvil sin tocar nada.

### D3. Filament 5 para el admin
- **Alternativas:** admin custom en Next.js con el estilo de la web.
- **Por qué:** un admin custom son 2-3 semanas de CRUD que no venden ropa. Filament da
  listas, formularios, validación y subida de imágenes leyendo los MISMOS modelos —
  una sola fuente de verdad para las reglas. Costo aceptado: el admin no tiene el look
  urbano del frontend (nadie lo ve salvo el equipo).

### D4. SQLite en desarrollo, MySQL en producción
- **Alternativas:** instalar MySQL en cada máquina del equipo.
- **Por qué:** onboarding en minutos (clonar + migrate), cero servicios corriendo, BD en
  un archivo. Las migrations son idénticas en ambos motores — solo cambia el `.env`.
  Riesgo controlado: diferencias sutiles entre motores (el CHECK de stock se aplica solo
  en MySQL; documentado en la migration).

## Base de datos

### D5. Migrations en vez de SQL directo
Ver [02-base-de-datos](02-base-de-datos.md). El esquema viaja en git y se reproduce
idéntico en cada máquina y en producción. `schema.sql` queda como *documentación de
referencia*, no como ejecutable.

### D6. El stock vive en `variants`, no en `products`
La decisión estructural más importante del proyecto. Un hoodie negro-M y negro-L son
dos realidades de inventario distintas. Guardar stock por producto obligaría a elegir:
o no hay tallas, o se rompe el stock. Costo aceptado: más filas y más código (selectores
de talla) — inevitable, la realidad del negocio es así.

### D7. `DECIMAL(10,2)` + precio como string en el JSON
Dinero exacto en BD y en tránsito. `0.30000000000000004` no existe en este proyecto.

### D8. `status` es ENUM pero `size` es VARCHAR
El estado es un conjunto cerrado que solo cambia por decisión deliberada (migration).
La talla crecerá (números de calzado) y un ENUM obligaría a `ALTER TABLE` constantes.
Criterio: cerrado → ENUM, expandible → VARCHAR + validación en app.

### D9. `has_stock` calculado, nunca guardado
Si se guardara, habría dos fuentes de verdad (la columna y las variantes) que se
desincronizan inevitablemente. Regla general: **no almacenes lo que puedes derivar**.

### D10. `slug` único además del `id`
URLs legibles y compartibles (`/producto/hoodie-oversize-graffiti` — mejor para SEO y
para WhatsApp) con PK numérica estable para las FKs. Lo mejor de ambos.

## API y contrato

### D11. Contrato escrito ANTES de los endpoints
Permite que A y C construyan el frontend contra datos falsos con la forma exacta, sin
esperar a B. La integración deja de ser un evento: es un cambio de URL. Ver
[01-arquitectura](01-arquitectura-y-principios.md) §2.

### D12. Todo envuelto en `data` + `meta` de paginación estilo Laravel
Respuestas uniformes: el cliente lee SIEMPRE `body.data`; los listados traen `meta`.
Una sola convención = cliente simple (ver `apiGet` y `apiList` en `api.ts`).

### D13. Prefijo `/api/v1`
Barato hoy, caro mañana. Cambios rompientes en el futuro → `/v2` y los clientes viejos
siguen vivos mientras migran. Costo hoy: teclear 3 caracteres.

### D14. Endpoint `ping` lo primero
El "hola mundo" de integración: valida routing, CORS, JSON y el cliente TS en 10 líneas.
Cuando algo no funcione en staging, ping divide el problema por la mitad (¿llega la
petición? ¿responde bien el backend?). Costo: nada. Todo sistema distribuido necesita un
healthcheck.

### D15. Listado liviano vs ficha completa
`GET /products` no manda variantes ni descripción: son 12–48 items por página. La ficha
trae todo. Ahorra bytes y consultas donde no se necesitan. Es el patrón
"lista vs detalle" de toda API.

### D16. Variantes con stock 0 SÍ se envían
Decisión de producto: mostrar la talla agotada tachada. La escasez visible vende y evita
el soporte de "¿tendán otra vez la M?". El `stock: 0` viaja y el frontend decide cómo pintarlo.

## Frontend

### D17. `"use client"` solo en ApiStatus
Máximo SSR (SEO) y mínimo JS enviado al navegador. La página es un Server Component
estático; solo el puntito de estado hidrata. Ver [04-nextjs](04-nextjs.md) §4.

### D18. Cliente tipado (`api.ts`) en vez de `fetch` sueltos
El contrato como tipos TS: `ProductDetail`, `PaginationMeta`... Si el backend cambia un
campo, error de compilación, no bug de producción. Además centraliza `no-store` y el
manejo de errores en un solo lugar (DRY).

### D19. Tailwind v4 + tokens por venir
El estilo vive junto al markup y el equipo comparte vocabulario visual. La identidad de
marca (equipo A) entrará como tokens CSS en `globals.css` — cambiar marca no será
rehacer componentes.

### D20. Carrito en localStorage + Zustand (decisión de plan)
Sin cuentas de usuario ni tabla de carritos: YAGNI. El carrito son datos del navegador
que solo él usa. Costo asumido: al limpiar el navegador se pierde — aceptable, si
querías el producto, lo compras. Se reevaluará si hay login de clientes.

## Backend

### D21. Seeders idempotentes (`updateOrCreate`)
El seeder corre y re-corre sin duplicar. No-idempotencia en seeders = la BD de dev se
corrompe la segunda vez que alguien corre `--seed`.

### D22. Settings en BD con caché cache-aside
El número de WhatsApp editable desde el panel sin redeploy. Se lee en cada página → se
cachea indefinidamente y se invalida al escribir. Patrón aplicable a cualquier
configuración de la tienda.

### D23. Scopes y accessors en modelos (no en controladores)
`Product::published()->featured()` es legible, reutilizable y tiene un solo dueño. Si
"publicado" cambia de definición (¿agotados incluidos?), se cambia UN método. DRY real:
sobre conocimiento, no sobre texto.

### D24. CORS por variable de entorno
`localhost:3000` en dev, el dominio de Vercel en producción — mismo código, distinta
configuración (12-factor). Y nunca `*` en producción: la API sería consumible por la web
de cualquiera.

### D25. Roles `admin`/`editor` ya en Fase 0
La columna cuesta añadirla ahora y cuesta MÁS añadirla con datos. Los dos roles del
equipo quedan modelados; los permisos finos llegan con Shield en Fase 3.

---

## Decisiones que se tomaron por ustedes (y pueden revocarse)

| Decisión | Motivo | Cómo revocarla |
|---|---|---|
| SQLite en dev | Ningún MySQL local disponible | Instalar MySQL + cambiar `.env` (documentado) |
| Geist como fuente | Placeholder hasta la identidad real | `layout.tsx` + `next/font` |
| Ámbar como acento | Combina con negro urbano | tokens en `globals.css` |
| Datos del seeder | Realistas para probar estados | Borrar cuando haya contenido real (Fase 1) |
