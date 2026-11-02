# 06 · Git y trabajo en equipo

Git no es "guardar versiones": es el protocolo de coordinación de 3 personas tocando el
mismo código a la vez. El flujo malo genera conflictos diarios; el bueno, ninguno.

---

## 1. El modelo mental en 4 áreas

```
Directorio de trabajo → (git add) → Staging → (git commit) → Historial → (git push) → GitHub
```

- **Staging**: la "lista de la compra" del commit — eliges qué entra en cada commit.
- **Commit**: foto instantánea del proyecto + mensaje que explica el porqué.
- **Branch** (rama): línea de tiempo paralela; `main` es la historia oficial.

## 2. Comandos del día a día

| Comando | Cuándo |
|---|---|
| `git status` / `git diff` | ANTES de cada add/commit — mira siempre lo que hiciste |
| `git switch -c feat/ficha-producto` | Crear rama para tu tarea (y salir de main) |
| `git add -p` | Añadir por trozos (el commit queda contado) |
| `git commit -m "..."` | Confirmar |
| `git push -u origin mi-rama` | Subir la rama la primera vez |
| `git pull --rebase origin main` | Traerte lo nuevo de main sin commits fantasma de merge |
| `git log --oneline --graph` | Entender la historia |
| `git restore <archivo>` | Descartar cambios de un archivo (⚠️ sin vuelta atrás) |

## 3. El flujo del equipo (GitHub Flow, adaptado a 3)

```
main ──●──●──●──●──●──        main = SIEMPRE funcionando (deployable)
        \    /       \
         ●──●         ●       ramas por tarea: feat/grid-productos, fix/cors-preflight
```

1. `main` está protegida: **nadie pushea directo** (se activa en Settings → Branches).
2. Cada tarea = rama `feat/...` o `fix/...` con nombre descriptivo.
3. Al terminar: **Pull Request** en GitHub + revisión de otro del equipo (code review).
4. CI verde y 1 aprobación → merge → borrar la rama.

Ramitas pequeñas (horas/días, no semanas) = conflictos mínimos.

## 4. Commits que cuentan historia

```
✅ "feat: ficha de producto con selector de tallas y colores"
✅ "fix: tallas agotadas ya no se pueden seleccionar ni añadir al carrito"
❌ "cambios"
❌ "fix fix"          ← ¿fix de qué? ¿por qué dos?
❌ "update files"     ← el 100% de los archivos son "files"
```

El primer commit de este repo (`6f3d832`) tiene cuerpo explicando qué contiene cada
parte: en un proyecto real, ese mensaje es lo único que tu yo-del-futuro tendrá cuando
algo rompa en 3 meses.

## 5. `.gitignore`: lo que NUNCA viaja

Ya excluido en este repo: `.env` (secretos), `vendor/` y `node_modules/` (se reconstruyen
con install), `*.sqlite` (datos locales), `.next/` (build).

¿Por qué? 1) Secretos en git = filtrados para siempre (git recuerda todo). 2) Deps de
50.000 archivos matan el repo. 3) Cada máquina genera sus propios artefactos.

Regla de oro: si un compañero clona el repo y sigue el README, debe tener todo
funcionando **sin pedirte ningún archivo por Discord**.

## 6. Conflictos de merge sin pánico

Un conflicto = dos personas tocaron las mismas líneas. Git te muestra:

```
<<<<<<< HEAD (tu rama)
precio en formato string
=======
precio como float
>>>>>>> main (lo que entra)
```

Pasos: `git status` para ver archivos en conflicto → abre cada uno → **elige/combina**
el código correcto (borra las marcas `<<<`, `===`, `>>>`) → `git add` → `git commit`.
No es un error: es git preguntándote "¿cuál de las dos verdades manda?".

## 7. Code review: leer el código del otro

Como revisor buscas: bugs obvios, nombres confusos, código que no sigue las reglas del
README, y N+1/fugas evidentes. Como autor: PR pequeño, descripción con captura, y
"pregunta tonta" no existe — la pregunta no hecha se convierte en bug en producción.

Cultura del equipo: se revisa el **código**, nunca la persona. "Aquí falta eager loading,
¿lo pensaste para N+1?" ≥ "esto está mal".

## 8. El repo como tablero de trabajo

GitHub Projects (o Issues) con columnas `Por hacer / En curso / Hecho`, una tarjeta por
tarea de la fase (el propio `plan-tienda-urbana.md` lista las tareas con checkboxes —
cada checkbox puede ser un issue). Así el equipo ve quién hace qué sin reuniones.

---

## ✅ Autoevaluación

1. Explica staging vs commit vs push con la analogía que quieras.
2. ¿Por qué nadie pushea a main y qué protege esa regla?
3. Tu `.env` tiene la contraseña de la BD de producción y ya lo commiteaste. ¿Qué haces? (pista: rotar credenciales, no solo borrar el archivo — git recuerda)
4. Simula un conflicto: crea la misma línea distinta en dos ramas y resuélvelo.
5. Escribe 3 mensajes de commit reales para: añadir al carrito, corregir un precio mal guardado, y actualizar el README.
