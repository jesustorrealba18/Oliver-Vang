import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Productos",
};

type ProductStatus = "published" | "sold_out" | "coming_soon";

type MockProduct = {
  id: number;
  name: string;
  slug: string;
  price: string;
  status: ProductStatus;
  category: string;
  colorFrom: string;
  colorTo: string;
};

/**
 * Datos de ejemplo mientras la API de Laravel implementa /products (Fase 1).
 * La forma de los campos sigue el contrato de api-contracts.md.
 */
const products: MockProduct[] = [
  {
    id: 1,
    name: "Hoodie Oversize Graffiti",
    slug: "hoodie-oversize-graffiti",
    price: "49.90",
    status: "published",
    category: "Hoodies",
    colorFrom: "#3f3f46",
    colorTo: "#09090b",
  },
  {
    id: 2,
    name: "Camiseta Boxy Logo",
    slug: "camiseta-boxy-logo",
    price: "24.90",
    status: "published",
    category: "Camisetas",
    colorFrom: "#52525b",
    colorTo: "#18181b",
  },
  {
    id: 3,
    name: "Boxer Tricolor Band",
    slug: "boxer-tricolor-band",
    price: "14.90",
    status: "published",
    category: "Underwear",
    colorFrom: "#44403c",
    colorTo: "#0c0a09",
  },
  {
    id: 4,
    name: "Gorra 5-Panel Nocturna",
    slug: "gorra-5-panel-nocturna",
    price: "19.90",
    status: "published",
    category: "Accesorios",
    colorFrom: "#3f3f46",
    colorTo: "#111827",
  },
  {
    id: 5,
    name: "Camiseta Vintage Wash",
    slug: "camiseta-vintage-wash",
    price: "27.90",
    status: "sold_out",
    category: "Camisetas",
    colorFrom: "#57534e",
    colorTo: "#1c1917",
  },
  {
    id: 6,
    name: "Pantalón Cargo Distopía",
    slug: "pantalon-cargo-distopia",
    price: "59.90",
    status: "coming_soon",
    category: "Pantalones",
    colorFrom: "#4c1d95",
    colorTo: "#0f0a1e",
  },
];

const statusLabel: Record<ProductStatus, string> = {
  published: "Disponible",
  sold_out: "Agotado",
  coming_soon: "Próximamente",
};

const statusStyle: Record<ProductStatus, string> = {
  published: "bg-emerald-400/15 text-emerald-300",
  sold_out: "bg-red-500/15 text-red-300",
  coming_soon: "bg-amber-400/15 text-amber-300",
};

export default function ProductosPage() {
  return (
    <main className="min-h-screen bg-neutral-950 text-neutral-100">
      <header className="border-b border-neutral-800">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
          <Link
            href="/"
            className="text-2xl font-black tracking-tighter transition hover:text-amber-400"
          >
            VANG
          </Link>

          <nav className="flex items-center gap-6 text-[11px] font-medium uppercase tracking-widest">
            <Link
              href="/"
              className="text-neutral-400 transition hover:text-neutral-100"
            >
              Inicio
            </Link>
            <Link href="/productos" className="text-amber-400">
              Productos
            </Link>
          </nav>
        </div>
      </header>

      <section className="mx-auto max-w-6xl px-6 pb-10 pt-16">
        <p className="text-[11px] font-medium uppercase tracking-[0.45em] text-amber-400">
          Drop 01 — Origen
        </p>

        <div className="mt-4 flex flex-wrap items-end justify-between gap-4">
          <h1 className="text-5xl font-black tracking-tighter sm:text-7xl">
            Productos
          </h1>
          <p className="text-sm text-neutral-400">
            {products.length} piezas · datos de ejemplo
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-6 pb-24">
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {products.map((product) => (
            <article
              key={product.id}
              className="group overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-900/50 transition hover:border-neutral-600"
            >
              <div
                className={`relative aspect-[4/5] overflow-hidden ${
                  product.status === "sold_out" ? "grayscale" : ""
                }`}
                style={{
                  background: `linear-gradient(145deg, ${product.colorFrom}, ${product.colorTo})`,
                }}
              >
                <span
                  aria-hidden
                  className="absolute inset-0 flex items-center justify-center text-8xl font-black text-white/10 transition duration-500 group-hover:scale-110"
                >
                  {product.name.charAt(0)}
                </span>

                <span
                  className={`absolute left-4 top-4 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest ${statusStyle[product.status]}`}
                >
                  {statusLabel[product.status]}
                </span>
              </div>

              <div className="p-5">
                <p className="text-[10px] uppercase tracking-[0.3em] text-neutral-500">
                  {product.category}
                </p>
                <h2 className="mt-1 text-lg font-bold leading-tight">
                  {product.name}
                </h2>

                <div className="mt-5 flex items-center justify-between gap-3">
                  <p className="font-semibold text-amber-400">
                    {product.price} €
                  </p>
                  <span className="text-[10px] uppercase tracking-widest text-neutral-600">
                    Ficha próximamente
                  </span>
                </div>
              </div>
            </article>
          ))}
        </div>

        <p className="mt-12 text-center text-xs leading-relaxed text-neutral-600">
          Fase 1 · catálogo de ejemplo.
          <br />
          Los datos reales llegarán desde la API de Laravel.
        </p>
      </section>
    </main>
  );
}
