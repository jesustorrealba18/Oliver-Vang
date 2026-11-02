# 03 · Laravel esencial

Laravel es el framework PHP más usado del mercado hispano. Dominar su *flujo* (no solo
sintaxis) es lo que se evalúa en entrevistas de backend PHP.

---

## 1. Qué te da un framework (que PHP "a pelo" no)

| Necesidad | PHP puro | Laravel |
|---|---|---|
| Enrutamiento | `if ($_GET['url'] == ...)` | `Route::get(...)` con grupos, prefijos y middleware |
| BD | `mysqli_*` a mano, SQL concatenado (riesgo inyección) | Eloquent/Query Builder con **prepared statements** |
| Migraciones | SQL manual en cada servidor | Versionadas en git, `php artisan migrate` |
| Auth | Sesiones caseras | Fortify/Sanctum auditados por millones |
| Envío de email, colas, caché, validación... | librerías sueltas | Integrado y consistente |

El coste: aprender sus convenciones. La ganancia: velocidad y seguridad por defecto.

## 2. Ciclo de vida de una request (simplificado)

```
Request HTTP
 → public/index.php (único punto de entrada)
 → bootstrap/app.php (carga config, .env, servicios)
 → Route matching (routes/api.php: /api/v1/ping)
 → Middleware en cadena (CORS, autenticación...)
 → Manejador (closure o Controller)
 → Modelos/BD si hace falta
 → Response (JSON) ← inversa por los middleware
```

`🔎 Dónde verlo:` `backend/routes/api.php` (la ruta ping es una *closure* inline), y corre
`php artisan route:list` para ver TODAS las rutas registradas, incluidas las de Filament.

## 3. Artisan: tu terminal manda

| Comando | Para qué |
|---|---|
| `php artisan serve` | Servidor de desarrollo :8000 |
| `php artisan migrate` | Ejecuta migrations pendientes |
| `php artisan migrate:fresh --seed` | Borra TODO y reconstruye + datos (⚠️ solo dev) |
| `php artisan tinker` | Consola interactiva (REPL) para probar modelos |
| `php artisan route:list` | Ver rutas registradas |
| `php artisan make:model X -m` | Crea modelo + su migration |
| `php artisan config:clear / cache:clear` | Cuando un cambio de .env "no se aplica" (gotcha clásico) |

**Gotcha del .env:** Laravel cachea la configuración. Si cambias el `.env` y "no ocurre
nada", corre `php artisan config:clear`.

## 4. Migrations: el esquema como código

Archivo PHP que describe un cambio de esquema, con `up()` (aplicar) y `down()` (revertir).

`🔎 Dónde verlo:` `backend/database/migrations/2026_11_02_000005_create_variants_table.php`

```php
Schema::create('variants', function (Blueprint $table) {
    $table->id();                                        // BIGINT UNSIGNED AUTO_INCREMENT PK
    $table->foreignId('product_id')                      // BIGINT UNSIGNED
          ->constrained('products')                      // FK → products.id
          ->cascadeOnDelete();                           // ON DELETE CASCADE
    $table->unique(['product_id', 'color', 'size']);     // regla de negocio
});
```

Por qué migrations y no SQL directo (resumen del capítulo de BD): **el esquema viaja en
git**. Cualquiera del equipo clona y con `migrate --seed` tiene la misma BD exacta. En
producción: `git pull && php artisan migrate`. Sin "¿quién creó esa columna?".

## 5. Eloquent: el ORM

Un modelo = una clase PHP = una tabla. [`backend/app/Models/Product.php`](../../backend/app/Models/Product.php):

### Relaciones (métodos que devuelven consultas)

```php
$product->category;   // belongsTo: el producto pertenece a UNA categoría
$product->variants;   // hasMany: tiene MUCHAS variantes
$category->products;  // hasMany al otro lado
```

Se definen una vez en el modelo y se usan en toda la app (DRY). En BD son JOINs; tú
piensas en objetos.

### `$fillable`: la asignación masiva

```php
protected $fillable = ['name', 'slug', 'price', ...];
```

`Product::create($request->all())` solo acepta esas columnas. Imagina un formulario
malicioso que añade `"role": "admin"` o `"price": 0` — mass assignment te protege:
**lo que no está en fillable, no entra**. Es la defensa nº1 de OWASP aplicada por Laravel.

### Casts: tipos al entrar y salir de la BD

```php
'price' => 'decimal:2',   // al serializar a JSON: "49.90" (string, como manda el contrato)
'featured' => 'boolean',
```

### Scopes: consultas reutilizables con nombre

```php
// Definido en el modelo
public function scopePublished(Builder $q): Builder { ... }

// Uso (Fase 1): legible y DRY
Product::published()->featured()->latest()->get();
```

### Accessors: campos calculados al vuelo

```php
// getHasStockAttribute → se usa como propiedad: $product->has_stock
public function getHasStockAttribute(): bool
```

**Principio:** los datos *derivados* (has_stock = suma de stock de variantes) NO se
guardan en BD — se calculan. Guardarlos crearía dos fuentes de verdad que se desincronizan.

## 6. Seeders: datos de desarrollo reproducibles

`🔎 Dónde verlo:` `backend/database/seeders/DatabaseSeeder.php`

Detalles que importan:

- `updateOrCreate([...criterio], [...datos])` es **idempotente**: puedes correr el seeder
  mil veces y no duplica filas. Un seeder con `create()` puro rompe al segundo `--seed`.
- `Setting::set()` después de insertar: fíjate cómo el modelo limpia la caché al escribir.
- Los datos son *representativos*: hay una talla con stock 0, un producto sold_out y un
  drop futuro — para que el frontend pruebe todos los estados sin tocar la BD.

## 7. Caché aplicada: settings

```php
Cache::rememberForever("setting.$key", fn () => ...);   // leer (y memorizar)
Cache::forget("setting.$key");                          // invalidar al escribir
```

Patrón **cache-aside**: leer es caro (BD) → se memoriza; escribir invalida → nunca sirve
dato viejo. Se usa para el número de WhatsApp, que se pide en cada página.

## 8. Sanctum y Filament (qué son, aunque aún no los usamos a fondo)

- **Sanctum:** tokens de API para apps (futuro: autenticar el admin desde Next.js).
  Instalado con `install:api`, pendiente de uso real.
- **Filament:** panel admin construido sobre Livewire (componentes React-like en PHP).
  Lee la BD a través de *los mismos modelos Eloquent* → las reglas de negocio son una
  sola vez. Su magia: describir un CRUD en una clase `Resource` y genera lista, formulario
  y validaciones. `🔎 backend/app/Providers/Filament/AdminPanelProvider.php` y el modelo
  `User::canAccessPanel()` que controla quién entra a `/admin`.

## 9. Composer: el npm de PHP

- `composer.json` = dependencias del proyecto; `composer.lock` = versiones exactas
  (se commitea; `vendor/` no, igual que `node_modules`).
- `composer install` instala desde el lock; `composer require x` añade e instala.
- PSR-4: el autoloader mapea `App\Models\Product` → `app/Models/Product.php`. Por eso
  el namespace SIEMPRE coincide con la ruta (convención sobre configuración).

---

## ✅ Autoevaluación

1. Dibuja de memoria el ciclo de vida de `GET /api/v1/ping` hasta el JSON.
2. ¿Por qué `migrate:fresh` está prohibido en producción pero es diario en dev?
3. ¿Qué evita `$fillable` y cómo se llama ese ataque?
4. ¿Por qué `has_stock` es un accessor y no una columna de la tabla products?
5. En tinker: lista los productos publicados con su categoría (sin N+1).
6. Cambias `.env`, nada pasa. ¿Qué comando corres y por qué?
