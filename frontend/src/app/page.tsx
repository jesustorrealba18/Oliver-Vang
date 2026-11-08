import Link from "next/link";

import { ApiStatus } from "@/components/api-status";

/**
 * Home de FASE 0 — plantilla base para validar el stack.
 * El equipo A (Frontend/Estilo) la reemplaza con la identidad
 * real de la marca: tokens de color, tipografías, hero y grid.
 */
export default function Home() {
  return (
    <main className="flex min-h-screen flex-col items-center justify-center bg-neutral-950 px-6 text-neutral-100">
      <p className="text-[11px] font-medium uppercase tracking-[0.45em] text-amber-400">
        Drop 01 — Origen · disponible
      </p>

      <h1 className="mt-5 text-8xl font-black tracking-tighter sm:text-9xl">
        VANG
      </h1>

      <p className="mt-4 max-w-md text-center text-sm leading-relaxed text-neutral-400">
        Ropa urbana y underwear. Catálogo en construcción — la venta cierra
        por WhatsApp.
      </p>

      <div className="mt-9 flex flex-col items-center gap-6">
        <Link
          href="/productos"
          className="group inline-flex items-center gap-3 rounded-full bg-amber-400 px-7 py-3 text-xs font-bold uppercase tracking-widest text-neutral-950 transition hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-400"
        >
          Ver productos
          <span
            aria-hidden
            className="transition-transform duration-300 group-hover:translate-x-1"
          >
            →
          </span>
        </Link>

        <ApiStatus />
      </div>

      <footer className="absolute bottom-6 text-[11px] uppercase tracking-widest text-neutral-600">
        Fase 0 · plantilla base — pendiente identidad de marca
      </footer>
    </main>
  );
}
