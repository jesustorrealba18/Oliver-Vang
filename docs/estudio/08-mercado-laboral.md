# 08 · Mercado laboral: lo que falta por dominar

Este proyecto te da fullstack Laravel + Next.js — un perfil muy contratable en el mercado
hispano. Esta guía cubre lo que **las ofertas piden** y este proyecto aún no toca, en
orden de retorno de inversión. Fuente: patrón típico de ofertas junior/semi de PHP+JS.

---

## Mapa del perfil completo

```
YA EN ESTE PROYECTO                    POR DOMINAR (este documento)
├── Laravel + Eloquent                 ├── Testing (Pest)          ← prioridad 1
├── Next.js + TypeScript               ├── Docker                  ← prioridad 2
├── MySQL + SQL                        ├── CI/CD (GitHub Actions)  ← prioridad 3
├── API REST + contratos               ├── Seguridad web (OWASP)   ← transversal
├── Git + trabajo en equipo            ├── Deploys reales          ← prioridad 4
└── Tailwind, Zustand, Framer          └── Inglés técnico leído    ← transversal
```

## 1. Testing — el diferenciador nº1 de un junior

El 90% de los candidatos junior no sabe testear; quien sabe, destaca solo.

- **Por qué se paga:** el testing cambia de "funciona en mi máquina" a "está demostrado
  que funciona". Las empresas que contratan para mantener código legado viven de esto.
- **Qué aprender:** **Pest** (el framework de tests de moda en Laravel, sintaxis
  dulce) — tests de feature para la API (GET /products devuelve 200 con forma del
  contrato) y tests unitarios para modelos (un producto con todas las variantes a 0 no
  tiene has_stock).
- **En este proyecto (Fase 4):** `composer require pestphp/pest --dev` + test del
  contrato: *la API nunca puede violar api-contracts.md, y un test lo demuestra en cada PR*.
- **Para el frontend:** Vitest (unidades) y Playwright (end-to-end: "añado al carrito y
  el mensaje de WhatsApp se genera bien").

## 2. Docker — "obligatorio" en 8 de 10 ofertas

- **Qué es:** contenedores = la app + sus dependencias aisladas en una caja reproducible.
  "En mi máquina funciona" muere aquí: la caja es idéntica en todas partes.
- **Conceptos:** imagen (plantilla) vs contenedor (instancia); Dockerfile (receta);
  docker-compose (orquestra varios servicios juntos).
- **En este proyecto:** un `docker-compose.yml` con MySQL + backend + frontend acabaría
  con el debate SQLite/MySQL: MySQL real en dev sin instalar nada. Ejercicio perfecto
  cuando llegue la Fase 3.
- **Mentalidad:** hoy todo se despliega en contenedores. No es opcional para backend.

## 3. CI/CD — GitHub Actions

Automatizar en cada PR: lint + tests + build. Si algo falla, el PR no se mezcla.

```yaml
# .github/workflows/ci.yml (futuro)
on: [pull_request]
jobs:
  backend:  → composer install, pest
  frontend: → npm ci, tsc, build
```

Media hora configurándolo y el equipo deja de subir a main cosas rotas. Es el "D" de
DevOps que ya puedes aplicar con tu cuenta de GitHub gratis.

## 4. Seguridad web — OWASP aplicado al proyecto

Lo que toda entrevista de backend pregunta, con lo que YA usamos:

| Riesgo | Defensa | En el proyecto |
|---|---|---|
| SQL Injection | Prepared statements | Eloquent SIEMPRE parametriza (nunca concatenar SQL) |
| Mass assignment | Lista blanca de columnas | `$fillable` en todos los modelos |
| Contraseñas en claro | Hash lento con sal | cast `'password' => 'hashed'` (bcrypt) |
| XSS | Escapar salida | React escapa por defecto; peligro: `dangerouslySetInnerHTML` (no usar) |
| CSRF | Token por sesión/formulario | Filament/Laravel lo llevan en formularios web |
| Secretos expuestos | .env fuera de git | `.gitignore` + `.env.example` |
| CORS abierto | Orígenes explícitos | `CORS_ALLOWED_ORIGINS` |

Además: rate limiting (throttle en endpoints de login — Fase 3), validación server-side
SIEMPRE (la del navegador es solo UX).

## 5. Deploys reales — convierte el proyecto en portfolio

Un proyecto en localhost no existe para un reclutador. El plan de este repo ya lo prevé:

- **Frontend → Vercel:** gratis, conectado al repo (push a main = deploy).
- **Backend → un VPS barato (Hetzner/Racknerd) con PHP + MySQL:** aquí es donde se
  APRENDE de verdad: nginx, permisos, HTTPS con Let's Encrypt, variables de entorno,
  `php artisan migrate` en producción, backups de BD.
- **Ejercicio definitivo:** dominio propio + HTTPS + la tienda viva con datos reales.
  Esa URL en el CV vale más que diez certificados.

## 6. Fundamentos que se dan por sabidos (y se preguntan)

- **Linux y terminal:** permisos, procesos, logs (`tail -f storage/logs/laravel.log`),
  ssh. El backend vive en Linux.
- **Estructura de datos básica:** array/map/set, complejidad O(n) intuitiva — explica
  por qué un índice cambia todo (capítulo 02).
- **JSON y formatos:** leer/escribir a mano sin librería.
- **Lectura de documentación:** la habilidad transversal. Los docs de Laravel y Next son
  el estándar oro: apréndete su índice.

## 7. Inglés técnico

No negociable para crecer sueldo: la documentación, los errores de consola, Stack
Overflow y las entrevistas técnicas de productoras internacionales. Meta práctica:
leer los errores SIEMPRE en inglés (búscalos tal cual), docs en inglés aunque cueste 2x.

## 8. Cómo estudiar (método > horas)

1. **Construye > consume:** cada concepto de estos apuntes, úsalo en el proyecto en la
   semana en que lo aprendas.
2. **Espaciado:** repasa un documento de esta carpeta por semana (rotación).
3. **Explica:** lo que no puedes explicar a tu equipo (verbalmente o en un README) no lo
   dominas. Los "✅ Autoevaluación" de cada doc son el filtro.
4. **Profundidad estratégica:** sé experto en lo que usa el proyecto (Laravel, Next,
   MySQL) y consciente de lo demás (Redis, colas, GraphQL: saber QUÉ problema resuelven).

## 9. El portfolio: cómo contar ESTE proyecto en una entrevista

- README con capturas, arquitectura (diagrama), y las decisiones — enlaza
  `07-decisiones-fase-0.md`: hablar de trade-offs te posiciona como semi-senior.
- "Tienda real con ventas por WhatsApp, 3 personas, API-first con contrato versionado"
  suena a proyecto; "mi proyecto de curso" no.
- Métricas concretas: nº de endpoints, cobertura de tests, Lighthouse > 90 (Fase 4).
- Guion de entrevista con este proyecto: 1) problema de negocio 2) arquitectura y por
  qué 3) una decisión difícil (variantes/stock) 4) un error que cometiste y cómo lo
  encontraste (¡ten la historia lista — el debugging del `Setting` sin import fue el primero!).

---

## ✅ Autoevaluación de carrera

1. ¿Cuál es tu siguiente inversión de aprendizaje y por qué es testing antes que Docker?
2. Escribe de memoria el pipeline CI ideal para este repo.
3. ¿Qué ataque previene `$fillable` y cómo se llama en la lista OWASP?
4. ¿Qué te falta para tener ESTE proyecto online con dominio propio? (lista concreta)
