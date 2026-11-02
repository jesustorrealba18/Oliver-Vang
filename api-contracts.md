# Contratos de API — Tienda de ropa urbana

## 1. ¿Qué es un contrato de API?

Un **contrato de API** es el acuerdo escrito entre el frontend y el backend sobre cómo van a comunicarse. No es código: es documentación que define cuatro cosas:

| Define | Ejemplo de este proyecto |
|---|---|
| Qué endpoints existen | `GET /api/v1/products` |
| Qué se envía | `?category=hoodies&page=2` |
| Qué se devuelve | el JSON exacto, campo por campo |
| Cómo son los errores | código HTTP + cuerpo del error |

**La analogía del menú de restaurante:** el cliente (frontend) no necesita saber cocinar ni entrar a la cocina. Le basta el **menú** (contrato) para saber qué puede pedir y qué recibirá. La cocina (backend) puede reorganizarse internamente, cambiar de chef o de proveedores — mientras el menú se cumpla, nadie se entera. Esa independencia interna es justo lo que permite que ustedes tres trabajen en paralelo.

**Por qué importa aquí:**
- Ustedes son 3. Mientras **B** construye la API, **A** maqueta la web con datos de ejemplo que respetan este contrato. Al integrar, todo encaja sin reescribir.
- Evita el clásico *"a mí me funcionaba"*: los bugs de integración se detectan antes de existir.
- Es documentación viva: quien vuelva al proyecto en 3 meses entiende cómo funciona sin leer el código.

**Regla de oro:** este documento se puede cambiar, pero nunca en silencio. Si alguien necesita tocar un campo, se avisa al equipo y se actualiza aquí primero. Luego se cambia el código.

---

## 2. Convenciones generales

- **Base URL:** `https://api.tudominio.com/api/v1`
  - El `/v1` es **versionado**: si mañana necesitan cambios que rompen el formato, crean `/v2` y el frontend viejo sigue funcionando mientras se migra.
- **Formato:** JSON, UTF-8, siempre. Sin excepciones.
- **Fechas:** ISO 8601 → `2026-10-30T18:00:00Z`
- **Precios:** strings (`"49.90"`), no números. JavaScript tiene errores clásicos con decimales (`0.1 + 0.2 === 0.30000000000000004`); el string llega limpio y se pinta tal cual.
- **IDs:** números enteros.
- **Todo envuelto en `data`:** objeto único → `{"data": {...}}`; lista → `{"data": [...], "meta": {...}}`. La uniformidad simplifica el frontend (una sola forma de leer respuestas).

### Paginación (formato estándar de Laravel)

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 12,
    "total": 118
  }
}
```

### Formato de errores (uniforme en toda la API)

```json
{
  "error": {
    "code": "not_found",
    "message": "Producto no encontrado"
  }
}
```

### Códigos de estado HTTP

| Código | Nombre | Cuándo se usa |
|---|---|---|
| 200 | OK | Petición exitosa |
| 201 | Created | Recurso creado (endpoints del admin) |
| 204 | No Content | Borrado exitoso — no devuelve cuerpo |
| 400 | Bad Request | Petición mal formada |
| 401 | Unauthorized | Sin sesión/token válido |
| 403 | Forbidden | Con sesión, pero sin permiso (editor tocando usuarios) |
| 404 | Not Found | Slug inexistente |
| 422 | Unprocessable Entity | Errores de validación |
| 429 | Too Many Requests | Demasiadas peticiones seguidas |
| 500 | Server Error | Bug del backend |

El código HTTP es la **primera señal** que lee el frontend: con `404` muestra "producto no encontrado"; con `500`, el error genérico y un aviso al equipo.

---

## 3. Endpoints

### 3.1 `GET /products` — listado del catálogo

**Query params (todos opcionales):**

| Param | Tipo | Ejemplo | Nota |
|---|---|---|---|
| `category` | slug | `hoodies` | filtra por categoría |
| `collection` | slug | `drop-01-origen` | filtra por drop |
| `search` | texto | `graffiti` | busca en nombre y descripción |
| `featured` | 0/1 | `1` | solo destacados |
| `sort` | enum | `newest` (defecto) · `price_asc` · `price_desc` | |
| `page` | int | `2` | |
| `per_page` | int | `12` (defecto, máx 48) | |

**Respuesta 200:**

```json
{
  "data": [
    {
      "id": 12,
      "name": "Hoodie Oversize Graffiti",
      "slug": "hoodie-oversize-graffiti",
      "price": "49.90",
      "status": "published",
      "main_image_url": "https://api.tudominio.com/storage/products/hoodie-1.webp",
      "category": { "name": "Hoodies", "slug": "hoodies" },
      "collection": { "name": "Drop 01 — Origen", "slug": "drop-01-origen" },
      "has_stock": true
    }
  ],
  "meta": { "current_page": 1, "last_page": 10, "per_page": 12, "total": 118 }
}
```

**Notas para el frontend:** el listado es deliberadamente *liviano* (no incluye variantes ni descripción) porque puede traer 12–48 productos por página. `has_stock` permite poner el "Agotado" sin pedir las variantes. La ficha completa está en 3.2.

### 3.2 `GET /products/{slug}` — ficha del producto

**Respuesta 200:**

```json
{
  "data": {
    "id": 12,
    "name": "Hoodie Oversize Graffiti",
    "slug": "hoodie-oversize-graffiti",
    "description": "Hoodie oversize 400g con serigrafía...",
    "price": "49.90",
    "status": "published",
    "featured": true,
    "images": [
      { "url": "https://api.tudominio.com/storage/products/hoodie-1.webp", "alt": "Hoodie frontal", "sort_order": 1 },
      { "url": "https://api.tudominio.com/storage/products/hoodie-2.webp", "alt": "Hoodie espalda", "sort_order": 2 }
    ],
    "variants": [
      { "id": 101, "color": "Negro", "color_hex": "#111111", "size": "S", "stock": 5 },
      { "id": 102, "color": "Negro", "color_hex": "#111111", "size": "M", "stock": 0 },
      { "id": 103, "color": "Negro", "color_hex": "#111111", "size": "L", "stock": 3 },
      { "id": 104, "color": "Beige", "color_hex": "#D9C7A7", "size": "M", "stock": 2 }
    ],
    "category": { "name": "Hoodies", "slug": "hoodies" },
    "collection": { "name": "Drop 01 — Origen", "slug": "drop-01-origen" }
  }
}
```

**Notas para el frontend:**
- Las variantes con `stock: 0` **sí se envían** (con id 102 arriba): la talla se muestra tachada y sin clic. Es psicología de venta: ver la talla agotada aumenta el deseo.
- El `id` de la variante es lo que guarda el carrito — no "producto + color + talla". Más simple y sin ambigüedad.
- `color_hex` pinta el circulito del selector de color.

**Error 404:**

```json
{ "error": { "code": "not_found", "message": "Producto no encontrado" } }
```

### 3.3 `GET /categories` — categorías para el menú y filtros

**Respuesta 200:**

```json
{
  "data": [
    { "id": 1, "name": "Camisetas", "slug": "camisetas", "image_url": null, "products_count": 23 },
    { "id": 2, "name": "Hoodies",   "slug": "hoodies",   "image_url": null, "products_count": 14 }
  ]
}
```

### 3.4 `GET /collections` — drops (pasados y futuros)

**Respuesta 200:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Drop 01 — Origen",
      "slug": "drop-01-origen",
      "cover_image_url": "https://api.tudominio.com/storage/collections/drop01.webp",
      "published_at": "2026-10-01T00:00:00Z",
      "is_upcoming": false
    },
    {
      "id": 2,
      "name": "Drop 02 — Noche Urbana",
      "slug": "drop-02-noche-urbana",
      "cover_image_url": "https://api.tudominio.com/storage/collections/drop02.webp",
      "published_at": "2026-11-15T00:00:00Z",
      "is_upcoming": true
    }
  ]
}
```

`is_upcoming` sale de comparar `published_at` con la fecha actual. Un drop futuro se muestra en la web como **countdown** ("próximamente"), sin precios ni compra.

### 3.5 `GET /settings` — configuración pública

Solo claves whitelisted (nunca devuelve settings internos). El frontend la cachea y no la pide en cada página.

**Respuesta 200:**

```json
{
  "data": {
    "whatsapp_number": "34600000000",
    "instagram_url": "https://instagram.com/tumarca"
  }
}
```

---

## 4. Cómo trabajar con este contrato en la práctica

**El flujo en equipo:**

1. **B** implementa cada endpoint para cumplir el contrato *exactamente* (nombres de campos incluidos).
2. **A y C** desarrollan la UI contra un **mock**: un JSON local con la forma del contrato (o una librería como MSW que intercepta el `fetch`). Cuando la API real está lista, se cambia la URL base y todo funciona.
3. Si aparece una necesidad nueva (p. ej. "necesito filtrar por color"), **no se improvisa**: se discute, se actualiza este documento, y *después* se programa en ambos lados.

**Reglas de oro:**

- **Añadir campos: OK.** Quitar o renombrar: **NO** dentro de la misma versión (rompe al otro lado).
- El frontend nunca "inventa" campos que no están aquí; si le faltan, se piden.
- Un endpoint que devuelve otra cosa que este documento = bug, aunque "funcione".

**Herramientas útiles (cuando el proyecto avance):**

- **Postman / Bruno** — colección compartida para probar endpoints a mano sin el frontend.
- **Laravel Scramble** — genera documentación OpenAPI interactiva (`/docs/api`) automáticamente desde el código. Imprescindible cuando la API crezca.
- **OpenAPI/Swagger** — el estándar de la industria para contratos; este documento hecho formal y validable por máquinas.
