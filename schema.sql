-- ============================================================
-- SCHEMA — Tienda de ropa urbana
-- MySQL 8.x / MariaDB 10.4+
-- ------------------------------------------------------------
-- NOTA: En Laravel esto NO se escribe como SQL directo, sino
-- como "migrations" (php artisan make:migration). Este archivo
-- es la REFERENCIA: es lo que las migrations deben producir.
-- Escribirlo primero obliga a pensar el modelo completo antes
-- de programar, y le sirve al equipo como documentación.
-- ============================================================

-- ------------------------------------------------------------
-- USERS — usuarios del panel de administración (Filament)
-- Solo el equipo. No hay registros públicos en esta fase.
-- ------------------------------------------------------------
CREATE TABLE users (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,               -- siempre hasheado (bcrypt), nunca texto plano
    role           ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    remember_token VARCHAR(100) NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- admin  = acceso total (usuarios, settings, catálogo)
-- editor = gestiona catálogo, no toca usuarios ni settings

-- ------------------------------------------------------------
-- CATEGORIES — categorías fijas del catálogo
-- Ej: Camisetas, Hoodies, Pantalones, Underwear, Accesorios
-- ------------------------------------------------------------
CREATE TABLE categories (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,           -- "hoodies" → se usa en la URL: /categoria/hoodies
    description TEXT NULL,
    image       VARCHAR(255) NULL,                      -- ruta relativa, ej: categories/hoodies.webp
    sort_order  INT NOT NULL DEFAULT 0,                 -- orden manual en el menú
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- COLLECTIONS — drops / colecciones
-- published_at en el futuro = "PRÓXIMAMENTE" (así se genera
-- hype antes de soltar el drop)
-- ------------------------------------------------------------
CREATE TABLE collections (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    slug         VARCHAR(170) NOT NULL UNIQUE,
    description  TEXT NULL,
    cover_image  VARCHAR(255) NULL,
    published_at TIMESTAMP NULL,                        -- NULL = borrador, no visible
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PRODUCTS — el producto "padre"
-- OJO: el PRECIO vive aquí, el STOCK NO (vive en variants)
-- ------------------------------------------------------------
CREATE TABLE products (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   BIGINT UNSIGNED NOT NULL,
    collection_id BIGINT UNSIGNED NULL,                 -- NULL = fuera de cualquier drop
    name          VARCHAR(150) NOT NULL,
    slug          VARCHAR(170) NOT NULL UNIQUE,         -- /producto/hoodie-oversize-graffiti
    description   TEXT NULL,
    price         DECIMAL(10,2) NOT NULL,               -- NUNCA FLOAT para dinero: errores de redondeo
    status        ENUM('published','sold_out','coming_soon','draft') NOT NULL DEFAULT 'draft',
    featured      TINYINT(1) NOT NULL DEFAULT 0,        -- aparece destacado en la home
    main_image    VARCHAR(255) NULL,                    -- portada para los listados
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    FOREIGN KEY (category_id)   REFERENCES categories(id)  ON DELETE RESTRICT,
        -- RESTRICT: no se puede borrar una categoría que tiene productos (evita huérfanos)
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL,
        -- si se borra el drop, el producto SOBREVIVE sin colección
    INDEX idx_products_status     (status),
    INDEX idx_products_category   (category_id),
    INDEX idx_products_collection (collection_id),
    INDEX idx_products_featured   (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Nota sobre 'sold_out': en teoría se puede calcular (todas las variantes a 0),
-- pero tenerlo explícito da control manual para forzar el "agotado" estético.

-- ------------------------------------------------------------
-- PRODUCT_IMAGES — galería (varias fotos por producto)
-- ------------------------------------------------------------
CREATE TABLE product_images (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    path       VARCHAR(255) NOT NULL,
    alt_text   VARCHAR(255) NULL,                       -- descripción: accesibilidad + SEO
    sort_order INT NOT NULL DEFAULT 0,                  -- se llama sort_order porque "order" es palabra reservada en SQL
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        -- borrar el producto borra sus fotos (no quedan imágenes huérfanas)
    INDEX idx_images_product (product_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- VARIANTS — la tabla MÁS IMPORTANTE del proyecto
-- Un hoodie negro en M y el mismo hoodie negro en L son
-- DOS FILAS DISTINTAS. El stock pertenece a la variante.
-- ------------------------------------------------------------
CREATE TABLE variants (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    color      VARCHAR(50) NOT NULL,                    -- "Negro", "Blanco", "Beige"...
    color_hex  CHAR(7) NULL,                            -- "#111111" → para pintar el circulito de color en la UI
    size       VARCHAR(20) NOT NULL,                    -- "XS","S","M","L","XL","42","UNICA"...
                                                         -- VARCHAR y no ENUM: el calzado usa números,
                                                         -- y cambiar un ENUM requiere ALTER TABLE
    sku        VARCHAR(60) NULL UNIQUE,                 -- referencia interna, opcional en fase 1
    stock      INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_variant (product_id, color, size),    -- impide duplicar "Negro / M" dos veces
    INDEX idx_variants_product (product_id),
    CONSTRAINT chk_stock_nonneg CHECK (stock >= 0)      -- MySQL 8.0.16+ lo hace cumplir de verdad
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- SETTINGS — configuración de la tienda (clave/valor)
-- El número de WhatsApp vive aquí: editable desde Filament
-- sin tocar código ni hacer redeploy
-- ------------------------------------------------------------
CREATE TABLE settings (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,         -- se evita llamarla "key": palabra reservada de MySQL
    setting_value TEXT NULL,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS DE EJEMPLO (los seeders de Laravel cargarían esto)
-- ============================================================
-- INSERT INTO categories (name, slug, sort_order) VALUES
--   ('Camisetas', 'camisetas', 1),
--   ('Hoodies', 'hoodies', 2),
--   ('Underwear', 'underwear', 3);

-- INSERT INTO collections (name, slug, published_at) VALUES
--   ('Drop 01 — Origen', 'drop-01-origen', '2026-10-01 00:00:00'),
--   ('Drop 02 — Noche Urbana', 'drop-02-noche-urbana', '2026-11-15 00:00:00');  -- futuro = próximamente

-- INSERT INTO products (category_id, collection_id, name, slug, price, status, featured) VALUES
--   (2, 1, 'Hoodie Oversize Graffiti', 'hoodie-oversize-graffiti', 49.90, 'published', 1);

-- INSERT INTO product_images (product_id, path, alt_text, sort_order) VALUES
--   (1, 'products/hoodie-graffiti-1.webp', 'Hoodie oversize graffiti frontal', 1),
--   (1, 'products/hoodie-graffiti-2.webp', 'Hoodie oversize graffiti espalda', 2);

-- INSERT INTO variants (product_id, color, color_hex, size, stock) VALUES
--   (1, 'Negro', '#111111', 'S', 5),
--   (1, 'Negro', '#111111', 'M', 0),   -- agotado: la web lo muestra tachado
--   (1, 'Negro', '#111111', 'L', 3),
--   (1, 'Beige', '#D9C7A7', 'M', 2);

-- INSERT INTO settings (setting_key, setting_value) VALUES
--   ('whatsapp_number', '34600000000'),
--   ('instagram_url', 'https://instagram.com/tumarca');
