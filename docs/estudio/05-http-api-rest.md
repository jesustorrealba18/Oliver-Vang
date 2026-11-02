# 05 · HTTP, REST y CORS

El protocolo que une frontend y backend. Entender HTTP es entender el 70% del desarrollo
web — las entrevistas lo preguntan siempre.

---

## 1. Anatomía de una request/response

```
GET /api/v1/products?category=hoodies&page=2 HTTP/1.1     ← método + ruta + query string
Host: api.vang.com                                        ← headers (metadatos)
Accept: application/json

HTTP/1.1 200 OK                                           ← status
Content-Type: application/json                            ← el cuerpo es JSON
{"data": [...], "meta": {...}}                            ← cuerpo
```

- **Query string** (`?categoria=...`): parámetros opcionales de un GET (filtros, paginación).
- **Headers:** metadatos de negociación — `Accept` (qué espero), `Content-Type` (qué envío),
  `Authorization` (quién soy — futuro), `Origin` (de dónde vengo → CORS).
- El contrato de este proyecto ([`api-contracts.md`](../../api-contracts.md)) fija
  método, query params y cuerpo de cada endpoint: **es este capítulo aplicado**.

## 2. Métodos HTTP y su intención

| Método | Intención | Ejemplo del proyecto | Idempotente |
|---|---|---|---|
| GET | Leer, nunca cambiar nada | `GET /products` | Sí |
| POST | Crear / disparar acción | crear producto (Fase 3) | No |
| PUT/PATCH | Actualizar (todo / parcial) | editar precio | Sí |
| DELETE | Borrar | borrar variante | Sí |

**Idempotente** = repetir la petición deja el servidor en el mismo estado. PUT precio=50
mil veces = precio 50; POST crear pedido mil veces = mil pedidos (por eso el botón de
"comprar" se deshabilita tras el clic).

## 3. Status codes: el idioma que el frontend lee primero

| Familia | Significado | Del proyecto |
|---|---|---|
| 2xx | Éxito | 200 OK, 201 Created (Fase 3), 204 vacío tras DELETE |
| 3xx | Redirección | 302 al login de Filament si no hay sesión |
| 4xx | **El cliente** se equivocó | 404 slug inexistente, 422 validación, 401 sin sesión |
| 5xx | **El servidor** falló | 500 bug del backend |

Regla de diseño: 401 = no sé quién eres; 403 = sé quién eres y no puedes. El frontend
decide con el código: 404 → "producto no encontrado", 500 → mensaje genérico + aviso al
equipo. Por eso el cliente (`api.ts`) lanza `ApiError` con status incluido.

## 4. REST: recursos, no acciones

REST modela **recursos** (sustantivos) con URLs predecibles:

```
✅ GET /api/v1/products/12     → el producto 12
✅ GET /api/v1/categories      → las categorías
❌ GET /api/v1/getProducts     → verbo en la URL: el método YA es el verbo
❌ GET /api/v1/products/list   → redundante
```

Colecciones en plural, filtros por query string, versionado (`/v1`) para poder evolucionar
sin romper clientes viejos. Nivel de madurez que aplicamos: recursos + códigos + JSON
uniforme (todo envuelto en `data`, errores con forma única).

## 5. CORS explicado de verdad (no copy-paste)

**El problema:** un navegador que carga `localhost:3000` NO puede leer respuestas de
`otro-dominio:8000` — es la *same-origin policy*, defensa contra sitios maliciosos que
quieren leer tus datos con TU sesión de otro sitio.

**La solución (CORS):** el **backend** declara "confío en estos orígenes" con el header:

```
Access-Control-Allow-Origin: http://localhost:3000
```

`🔎 Dónde verlo:` `backend/config/cors.php` lee `CORS_ALLOWED_ORIGINS` del `.env`.
Verificado en la Fase 0: el ping desde el navegador funciona; desde otro dominio, bloqueado.

Tres detalles que se preguntan en entrevistas:
1. CORS lo **ejecuta el navegador**, no el servidor — el server solo declara la política.
2. Con métodos "no simples" (POST con JSON), el navegador manda antes un **preflight**
   (`OPTIONS`) a preguntar permiso.
3. Desactivar CORS con `*` en producción = cualquiera consume tu API desde su web.

## 6. Paginación: por qué el listado devuelve 12 y no 400

Cargar 400 productos = respuesta lenta + navegador ahogado. Paginación por página
(offset): `?page=2&per_page=12` con `meta.total` para pintar "118 productos".

```
{"data": [...12 items...], "meta": {"current_page":1, "last_page":10, "total":118}}
```

El formato es el de Laravel (paginate()) a propósito: cero código extra del lado del
backend para el meta. En apps con datos que cambian mucho en vivo existe la paginación
por cursor — apunte para estudiar, no para hoy.

## 7. Errores con forma única

```json
{ "error": { "code": "not_found", "message": "Producto no encontrado" } }
```

El `code` es para el **programa** (switch del frontend: `if code === 'not_found'`), el
`message` para el **humano**. Antipatrón clásico de junior: devolver 200 con
`{"success": false}` — el código HTTP ES información; úsala.

## 8. Autenticación: sesión vs token (mapa del tema)

| | Cookie de sesión | Token (Sanctum/JWT) |
|---|---|---|
| Dónde vive | Cookie del navegador (httpOnly) | Header `Authorization: Bearer ...` |
| Estado en servidor | Sí (tabla sessions) | No (el token firma la identidad) |
| Uso típico | Apps web monolíticas | APIs para SPAs/móvil |
| En el proyecto | Panel Filament | Fase 3+ si Next habla con endpoints privados |

El panel Filament usa cookies de sesión (nació así, seguro por defecto). El SPA público
ni las necesita: la API del catálogo es abierta.

---

## ✅ Autoevaluación

1. Escribe de memoria la request completa (método + ruta + query) para "hoodies publicados, página 2".
2. Diferencia 401/403/404/422 con un ejemplo del proyecto para cada uno.
3. ¿Quién ejecuta CORS, quién lo declara y qué es el preflight?
4. ¿Por qué el botón de enviar un pedido debe ser idempotente o deshabilitarse?
5. ¿Por qué "todo envuelto en data" hace la vida más fácil al frontend? (pista: una sola forma de leer respuestas)
