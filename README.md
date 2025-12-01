# Proyecto Cortes De Pelo

## Tecnologias Utilizadas:
- Electron
- Node
- Java Script
- CSS
- HTML
- Sqlite
- XML

## Estructura del Proyecto:
````Bash
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
````

