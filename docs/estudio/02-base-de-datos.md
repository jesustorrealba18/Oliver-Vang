# 02 · Bases de datos relacionales

Todo lo aplicado sobre el esquema real del proyecto. Para estudiarlo, abre
[`schema.sql`](../../schema.sql) y las migrations en `backend/database/migrations/` en paralelo.

---

## 1. Por qué relacional (y no MongoDB)

Los datos de una tienda son **relacionales por naturaleza**: un producto PERTENECE a una
categoría, TIENE variantes, PERTENECE a un drop. Las relaciones son restricciones que la
BD *hace cumplir*. Con MySQL (y un ORM) la integridad la garantiza el motor; con un
documento JSON habría que programarla a mano y vivir con bugs.

Regla de mercado: el 90% de los proyectos necesitan relacional. NoSQL brilla cuando la
estructura es variable o la escala es brutal — no es nuestro caso.

## 2. Claves primarias y foráneas

```sql
products.id            → clave primaria (PK): identifica UNA fila, única, nunca NULL
variants.product_id    → clave foránea (FK): apunta a la PK de products
```

- Usamos `id` autoincremental como PK técnica y `slug` como identidad **legible para URLs**
  (`/producto/hoodie-oversize-graffiti`). El slug lleva un `UNIQUE` para que sea imposible
  duplicarlo.
- ¿Por qué no usar el slug como PK? Porque renombrar un producto rompería todas las FKs.
  PK estable + slug único de cara afuera es el patrón estándar.

## 3. Los tres comportamientos de borrado (ON DELETE)

Es la decisión más ignorada por juniors y la que más datos destruye/ salva:

| Comportamiento | En el proyecto | Significado |
|---|---|---|
| `RESTRICT` | category_id → categories | No puedes borrar una categoría con productos. Primero mueves o borras los productos: evita productos huérfanos apuntando a nada |
| `CASCADE` | product_id en images/variants | Borrar el producto borra sus fotos y variantes: no tiene sentido una talla suelta sin producto |
| `SET NULL` | collection_id → collections | Borrar el drop NO borra el producto; solo lo deja sin colección. El daño mínimo |

Pregúntate siempre: **si borro esta fila, qué debe pasar con las que la apuntan**.

## 4. Normalización: por qué el stock vive en `variants`

La normalización elimina **redundancia** y **anomalías**. Mira la diferencia:

```
❌ SIN normalizar (todo en products):
  (1, 'Hoodie Graffiti', negro-M, stock 3)
  (2, 'Hoodie Graffiti', negro-L, stock 2)   ← el nombre se repite: si lo renombras,
                                               hay que tocar N filas y puede quedar
                                               'Hoodie Grafiti' en una (anomalía)

✅ NORMALIZADO (3ª forma):
  products:  (1, 'Hoodie Graffiti', 49.90, ...)     ← el producto, una vez
  variants:  (101, producto 1, Negro, M, stock 3)
             (102, producto 1, Negro, L, stock 2)   ← el stock, por combinación
```

La regla informal de la 3ª forma normal (3FN): *cada columna depende de la clave, de
toda la clave y de nada más que la clave*. El stock depende de (producto, color, talla)
→ su sitio es la tabla que representa esa combinación: `variants`.

## 5. Índices: el cómo y el porqué

Un índice es como el **índice de un libro**: estructura ordenada (B-tree) que evita leer
la tabla completa. Sin índice, `WHERE status = 'published'` revisa TODAS las filas
(full scan). Con él, salta directo.

En el proyecto:

```sql
INDEX idx_products_status (status)      → el listado público filtra por status
INDEX idx_products_category (category_id) → filtrar por categoría
INDEX idx_products_featured (featured)  → la home pide destacados
UNIQUE uq_variant (product_id, color, size) → índice ÚNICO: además de acelerar,
                                              es una regla de negocio que la BD impone
```

**Coste real:** cada índice hace más lento cada INSERT/UPDATE (hay que mantener los
"índices de libro" actualizados). No se indexa todo: se indexa lo que se *busca*.
Por eso `description` no tiene índice y `slug` sí (se busca por él constantemente).

## 6. Dinero: DECIMAL, nunca FLOAT

```sql
price DECIMAL(10,2)   -- 10 dígitos en total, 2 decimales: hasta 99.999.999,99
```

Los FLOAT/DOUBLE son **coma flotante binaria**: `0.1` no es representable exactamente en
binario, igual que 1/3 no lo es en decimal. Resultado: `0.1 + 0.2 = 0.30000000000000004`.
Con dinero, eso son descuadres que aparecen a los mil pedidos. DECIMAL guarda el número
*exacto*. Y por la misma razón, el contrato manda el precio como **string** `"49.90"` en
el JSON: JavaScript también usa coma flotante.

## 7. Restricciones como reglas de negocio

- `CHECK (stock >= 0)` — la BD se niega a stock negativo. Última línea de defensa: aunque
  la app tenga un bug, la BD protege el dato.
- `ENUM('published','sold_out','coming_soon','draft')` — solo esos valores son posibles.
- `UNIQUE` — imposibilita duplicados a nivel de almacenamiento.

Idea clave para interviews: **la validación en la app es UX; la restricción en la BD es
verdad**. Las dos capas conviven.

## 8. ¿Por qué `size` es VARCHAR y `status` es ENUM? (decisión aparentemente inconsistente)

- `status` tiene 4 valores **cerrados y controlados por código**: si mañana hay un 5º
  estado, se añade en una migration deliberada. El ENUM ahorra espacio y documenta.
- `size` crece con el negocio (XS…XXL, 40…45 de calzado, "ÚNICA"): un ENUM obligaría a
  `ALTER TABLE` constantemente. VARCHAR + validación en la app es flexible.

Criterio: **ENUM para lo cerrado, VARCHAR para lo que el negocio puede expandir**.

## 9. Transactions (concepto — se usan en Fase 3)

Una transacción agrupa varias operaciones en un *todo o nada*:

```php
DB::transaction(function () {
    $variant->decrement('stock');
    // ... registrar el pedido (fase futura)
});
```

Si algo falla a mitad, se hace ROLLBACK y queda como si nada hubiese pasado. La próxima
vez que se descuenta stock al confirmar un pedido por WhatsApp, debe ser transaccional:
"descontar stock y registrar venta" no puede quedar a medias (consistencia).

## 10. El problema N+1 (preview de la Fase 1)

```php
$products = Product::all();                 // 1 consulta
foreach ($products as $p) $p->variants;     // +1 consulta POR producto → 49 consultas!
```

Solución: **eager loading** → `Product::with('variants')->get()` (2 consultas, siempre 2).
Es el problema nº1 de rendimiento con ORMs y la pregunta de entrevista nº1 sobre Eloquent.

## 11. SQL diario mínimo (con su equivalente Eloquent)

| SQL | Eloquent |
|---|---|
| `SELECT * FROM products WHERE status='published'` | `Product::where('status','published')->get()` / `->published()->get()` |
| `SELECT * FROM products ORDER BY created_at DESC LIMIT 12` | `Product::latest()->limit(12)->get()` |
| `SELECT * FROM variants WHERE product_id = 1` | `Product::find(1)->variants` (relación) |
| `JOIN categories ...` | `Product::with('category')->...` (eager loading) |
| `SUM(stock)` | `$product->variants()->sum('stock')` |

**Ejercicio:** abre `php artisan tinker` y reproduce cada fila de la tabla sobre los datos
del seeder. Tinker es el laboratorio de Laravel.

---

## ✅ Autoevaluación

1. ¿Por qué RESTRICT en categoría y CASCADE en variantes? ¿Qué pasaría al revés?
2. Diseña de memoria las tablas de "pedidos" y "lineas_de_pedido" con sus FKs y comportamientos de borrado.
3. ¿Por qué el índice sobre slug y no sobre description?
4. Explica a alguien el problema N+1 con una analogía (pista: profesor que llama a lista).
5. ¿Por qué `0.1 + 0.2 !== 0.3` en JavaScript y qué hacemos en este proyecto para esquivarlo?
