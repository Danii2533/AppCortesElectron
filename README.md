# 📦 Proyecto Electron — Documentación Completa

## 📝 Descripción General

Este proyecto es una aplicación de escritorio construida con **Electron**, que integra un entorno web moderno con una base de datos local, diversos recursos estáticos y scripts para empaquetar la app de manera profesional.

Permite combinar tecnologías del ecosistema **JavaScript/TypeScript** con funcionalidades de escritorio como acceso al sistema de archivos, ventanas nativas y empaquetado instalable.

---

## 🚀 Tecnologías Utilizadas

### Frontend & Lógica

* **Electron** — Motor principal de la aplicación de escritorio.
* **Node.js** — Entorno de ejecución backend.
* **JavaScript / TypeScript** — Lógica de la aplicación.
* **HTML5 / CSS3** — Interfaz de usuario.
* **Electron Forge** — Empaquetado y distribución.

### Base de Datos

* Base de datos local almacenada en el directorio `/BBDD` (probablemente SQLite o JSON — puedes indicarme cuál exactamente).

### Herramientas y Configuración

* **npm / package.json** — Gestión de dependencias.
* **Electron Forge Config** — Configuración del empaquetado (`forge.config.js`).
* **VSCode** — Configuración del IDE (`.vscode/`).
* **node_modules** — Dependencias del proyecto.

### Recursos del Proyecto

* Carpeta `/resources` con imágenes, fuentes, configuraciones, iconos y otros assets de la aplicación.
* Carpeta `/GuiaDeEstilos` con lineamientos visuales.

---

## 📁 Estructura Completa del Proyecto

```
ElectronApp/
├─ .vscode/
│   ├─ settings.json
│   └─ extensions.json
│
├─ BBDD/
│   └─ (archivos de base de datos)
│
├─ GuiaDeEstilos/
│   └─ (documentación, estilos, recursos UI)
│
├─ node_modules/
│   └─ (dependencias instaladas)
│
├─ resources/
│   ├─ images/
│   │   └─ (iconos, recursos gráficos)
│   ├─ icons/
│   ├─ html/
│   │   ├─ index.html
│   │   └─ vistas/*.html
│   └─ css/
│       ├─ main.css
│       └─ estilos adicionales
│
├─ src/
│   ├─ electron/
│   │   ├─ main.ts
│   │   ├─ preload.ts
│   │   ├─ ipc/
│   │   │   ├─ handlers.ts
│   │   │   └─ canales.ts
│   │   └─ windows/
│   │       ├─ mainWindow.ts
│   │       └─ otros.ts
│   │
│   ├─ renderer/
│   │   ├─ scripts/
│   │   │   ├─ ui.js
│   │   │   ├─ eventos.js
│   │   │   └─ lógica.js
│   │   └─ styles/
│   │       └─ renderer.css
│   │
│   └─ utils/
│       ├─ fs.ts
│       ├─ paths.ts
│       └─ helpers.ts
│
├─ forge.config.js
├─ package.json
├─ package-lock.json
└─ .gitignore
```

---

## 🧱 Arquitectura del Proyecto

### 1. Capa Principal (Main Process – Electron)

* Ubicada en `src/electron/main.ts`
* Responsable de:

  * Crear ventanas.
  * Gestionar menú y accesos nativos.
  * Manejar procesos IPC.

### 2. Preload Script

* `src/electron/preload.ts`
* Funciona como puente seguro entre la UI y Node.js.

### 3. Renderer (Interfaz gráfica)

* `resources/html/`
* `resources/css/`
* `src/renderer/`
* Incluye **HTML + CSS + JS** que conforman la interfaz.

### 4. Comunicación IPC

* Permite que la UI interactúe con el sistema operativo mediante:

  * `src/electron/ipc/`

---

## 🧪 Scripts Disponibles (desde package.json)

* `npm start` — Ejecuta Electron en modo desarrollo.
* `npm run make` — Empaqueta la aplicación.
* `npm run package` — Crea versión portable.
* `npm run lint` — Analiza errores de estilo.
* `npm run build` — Compila TypeScript (si aplica).

---

## 📦 Empaquetado

El archivo `forge.config.js` gestiona:

* Configuración de instaladores.
* Iconos de la app.
* Plataformas destino (Windows/Linux/Mac).
* Paths de recursos.

---

## 📚 Posibles Mejoras

* Implementar tests automatizados (Jest / Vitest).
* Añadir soporte a **SQLite** o **IndexedDB** si aún no se usa.
* Crear un sistema modular para ventanas Electron.
* Integrar un framework de UI moderno (React, Vue, Svelte).
* Gestionar estados globales con Redux o Zustand.
* Documentar API interna de IPC.

## Link Conceptos

[Enlaze de Stich:](https://stitch.withgoogle.com/projects/4679072098660141080)

