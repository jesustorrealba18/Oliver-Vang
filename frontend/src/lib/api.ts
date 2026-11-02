/**
 * Cliente de la API — implementa el CONTRATO (api-contracts.md).
 *
 * Reglas del contrato aplicadas aquí:
 *  - Todo JSON, respuestas envueltas en "data"
 *  - Errores con forma { error: { code, message } }
 *  - Precios como string ("49.90"), jamás number
 *
 * Si la API cambia, primero se actualiza api-contracts.md (nunca en silencio).
 */

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api/v1";

/** Error de la API con el código del contrato (not_found, etc.) */
export class ApiError extends Error {
  constructor(
    public code: string,
    message: string,
    public status: number,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

/* ------------------------- Tipos del contrato ------------------------- */

export type ProductStatus = "published" | "sold_out" | "coming_soon" | "draft";

export interface CategoryRef {
  name: string;
  slug: string;
}

export interface ProductListItem {
  id: number;
  name: string;
  slug: string;
  price: string;
  status: ProductStatus;
  main_image_url: string | null;
  category: CategoryRef;
  collection: CategoryRef | null;
  has_stock: boolean;
}

export interface ProductVariant {
  id: number;
  color: string;
  color_hex: string | null;
  size: string;
  stock: number;
}

export interface ProductImage {
  url: string;
  alt: string | null;
  sort_order: number;
}

export interface ProductDetail extends ProductListItem {
  description: string | null;
  featured: boolean;
  images: ProductImage[];
  variants: ProductVariant[];
}

export interface CategoryItem {
  id: number;
  name: string;
  slug: string;
  image_url: string | null;
  products_count: number;
}

export interface CollectionItem {
  id: number;
  name: string;
  slug: string;
  cover_image_url: string | null;
  published_at: string;
  is_upcoming: boolean;
}

export interface PingData {
  message: string;
  time: string;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

/* ------------------------------ Cliente ------------------------------ */

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(`${API_URL}${path}`, {
    ...init,
    cache: "no-store",
    headers: { Accept: "application/json", ...init?.headers },
  });

  const body = await res.json().catch(() => null);

  if (!res.ok) {
    throw new ApiError(
      body?.error?.code ?? "unknown",
      body?.error?.message ?? `Error HTTP ${res.status}`,
      res.status,
    );
  }

  return body as T;
}

/** GET de objeto único: desenvuelve "data" (p. ej. ficha de producto) */
export async function apiGet<T>(path: string, init?: RequestInit): Promise<T> {
  const body = await request<{ data: T }>(path, init);
  return body.data;
}

/** GET de listado paginado: devuelve data + meta según el contrato */
export async function apiList<T>(
  path: string,
  init?: RequestInit,
): Promise<{ data: T[]; meta: PaginationMeta }> {
  return request<{ data: T[]; meta: PaginationMeta }>(path, init);
}
