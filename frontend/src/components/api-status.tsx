"use client";

import { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { apiGet, type PingData } from "@/lib/api";

/**
 * Indicador de conexión con la API (endpoint /ping del contrato).
 * Valida el stack completo en local y staging: si está verde,
 * el frontend y el backend están hablando.
 */
export function ApiStatus() {
  const [state, setState] = useState<"checking" | "online" | "offline">("checking");
  const [time, setTime] = useState<string | null>(null);

  useEffect(() => {
    apiGet<PingData>("/ping")
      .then((data) => {
        setState("online");
        setTime(data.time);
      })
      .catch(() => setState("offline"));
  }, []);

  const color =
    state === "online"
      ? "bg-emerald-400"
      : state === "offline"
        ? "bg-red-500"
        : "bg-amber-400";

  const label =
    state === "online"
      ? `API conectada${time ? ` · ${new Date(time).toLocaleTimeString("es")}` : ""}`
      : state === "offline"
        ? "API sin conexión (¿está corriendo php artisan serve?)"
        : "Comprobando API…";

  return (
    <div className="flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-xs text-neutral-400">
      <motion.span
        className={`inline-block h-2 w-2 rounded-full ${color}`}
        animate={state === "checking" ? { opacity: [1, 0.3, 1] } : { opacity: 1 }}
        transition={{ repeat: Infinity, duration: 1.2 }}
      />
      <span>{label}</span>
    </div>
  );
}
