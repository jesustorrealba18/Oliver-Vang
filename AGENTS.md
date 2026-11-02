# Vang — Tienda de ropa urbana (web) · Contexto del proyecto

Web de ropa underwear/urbana con identidad visual fuerte. **Fase 1: catálogo completo
sin pasarela de pago** — la venta se concreta por WhatsApp. Equipo de 3 personas:
A (Frontend/Estilo), B (Backend/API), C (Fullstack/Admin).

## Stack

- **Frontend:** Next.js (App Router) + Tailwind CSS + Framer Motion → deploy en Vercel
- **Backend:** Laravel como API REST JSON + Filament (panel admin) → deploy en host PHP/MySQL o VPS
- **BD:** MySQL · **Carrito:** Zustand + localStorage (sin backend)

## Documentos clave (leer antes de programar)

- `plan-tienda-urbana.md` — plan completo: fases, división de roles, riesgos
- `schema.sql` — esquema de referencia de la BD (las migrations de Laravel deben producir esto)
- `api-contracts.md` — contratos de la API: endpoints y JSON exacto. **Nunca cambiar en silencio**

## Reglas del proyecto

- El **stock vive en `variants`** (color + talla), nunca en `products`
- Precios en `DECIMAL(10,2)`, nunca FLOAT
- Respuestas de la API: precios como strings (`"49.90"`), todo envuelto en `data`
- Estructura del repo: `/frontend` (Next.js) + `/backend` (Laravel + Filament)
- Responder siempre en español

## Estado (2026-10-30)

Fase 0 no iniciada. Falta: crear el repo, proyectos base Laravel y Next.js,
acordar el contrato de la API antes de escribir endpoints.
