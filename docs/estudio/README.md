# 📚 Apuntes de estudio — VANG

Esta carpeta documenta **por qué** se tomó cada decisión del proyecto y qué hay que dominar
para entenderla (y para defenderla en una entrevista de trabajo). No es teoría suelta:
cada concepto apunta a archivos concretos de este repo.

## Cómo estudiar estos apuntes

1. Lee el documento **antes o después** de tocar el código de la fase, nunca "algún día".
2. Abre los archivos que menciona cada sección (`🔎 Dónde verlo en el proyecto`).
3. Responde las preguntas de `✅ Autoevaluación` sin mirar. Si fallas, relees.
4. Regla de oro: **cuando no entiendas una línea del código, búscala aquí**. Si no está, es deuda de documentación: pregunta.

## Índice y orden recomendado

| # | Documento | Qué cubre | Cuándo leerlo |
|---|---|---|---|
| 01 | [Arquitectura y principios](01-arquitectura-y-principios.md) | Cliente-servidor, contract-first, 12-factor, KISS/YAGNI | Fase 0 |
| 02 | [Bases de datos relacionales](02-base-de-datos.md) | Normalización, FKs, índices, DECIMAL vs FLOAT | Fase 0 |
| 03 | [Laravel esencial](03-laravel.md) | Ciclo de vida, migrations, Eloquent, seeders | Fase 0 |
| 04 | [Next.js esencial](04-nextjs.md) | Server vs Client Components, fetching, Tailwind | Fase 0 |
| 05 | [HTTP, REST y CORS](05-http-api-rest.md) | El protocolo que une los dos mundos | Fase 0–1 |
| 06 | [Git y trabajo en equipo](06-git-y-equipo.md) | Ramas, PRs, commits, conflictos (3 personas) | Fase 0 |
| 07 | [Decisiones de la Fase 0](07-decisiones-fase-0.md) | **Cada decisión con sus alternativas y su porqué** | Fase 0 |
| 08 | [Mercado laboral: lo que falta por dominar](08-mercado-laboral.md) | Testing, Docker, CI/CD, seguridad, portfolio | Siempre |

## Documentos por fase

Cada fase del proyecto tendrá su propio documento de decisiones:

- [x] **Fase 0 — Fundamentos** → `07-decisiones-fase-0.md`
- [ ] Fase 1 — Catálogo público → `09-decisiones-fase-1.md` (se crea al construirla)
- [ ] Fase 2 — Venta por WhatsApp → se crea al construirla
- [ ] Fase 3 — Admin (Filament) → se crea al construirla
- [ ] Fase 4 — Pulido y deploy → se crea al construirla

## Mapa mental del proyecto en una frase

> Un **frontend Next.js** consume por HTTP una **API REST versionada** construida con
> **Laravel**, que guarda los datos en una **base de datos relacional** modelada con
> normalización (el stock vive en la variante), coordinados los dos equipos por un
> **contrato de API escrito antes que el código**.
