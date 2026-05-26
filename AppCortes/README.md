# 💈 Ethan Cuts - Sistema de Gestión de Barbería

Bienvenido a **Ethan Cuts**, una aplicación de escritorio premium desarrollada con **Electron** para la gestión integral de barberías y salones de estética. Este sistema combina una interfaz ágil e interactiva de escritorio con un potente backend API en **PHP** y una base de datos relacional **MySQL**.

---

## 🚀 Características Principales

*   **Pantalla de Carga Dinámica (Splash Screen):** Animación de inicio elegante que realiza comprobaciones del sistema y verifica la conectividad de la API en tiempo real.
*   **Gestión de Citas (Agenda):** Calendario y control de reservas con estados dinámicos (*pendiente*, *confirmada*, *en curso*, *completada*, *cancelada*), asignando clientes, barberos y cortes específicos.
*   **Base de Clientes:** Fichero completo de clientes con historial de visitas, detalles de contacto y seguimiento personalizado.
*   **Galería de Estilos (Cortes):** Catálogo visual interactivo para almacenar y buscar cortes de pelo con etiquetas descriptivas.
*   **Panel de Configuración:** Configuración general del sistema con una guía de estilo integrada.
*   **Seguridad Robustecida:**
    *   Cifrado de datos sensibles en el lado del cliente mediante algoritmos criptográficos personalizados (`encryptor.cjs`).
    *   Seguridad en el servidor mediante hash seguro de contraseñas y sanitización de consultas con `PDO` preparado.
*   **Control de Roles:** Distinción clara entre perfiles de **Administrador** (acceso total) y **Peluquero** (operaciones diarias).

---

## 🛠️ Arquitectura y Tecnologías

El sistema está desarrollado bajo una arquitectura cliente-servidor distribuida:

1.  **Frontend (Cliente de Escritorio):**
    *   **Electron (v39.0):** Para empaquetar la aplicación de escritorio nativa multiplataforma.
    *   **HTML5 & CSS3 Vanilla:** Diseño de interfaz moderna, fluido y sin dependencias externas complejas.
    *   **JavaScript (ES6+):** Lógica interactiva y comunicación de procesos IPC (*Inter-Process Communication*) mediante un puente seguro (`preload.js`).
2.  **Backend (API de Servicios):**
    *   **PHP:** API RESTful modular para el procesamiento seguro de datos y autenticación de usuarios.
    *   **CORS habilitado:** Configuración flexible en cabeceras para permitir peticiones seguras desde el entorno de escritorio de Electron.
3.  **Base de Datos:**
    *   **MySQL:** Estructura relacional óptima con índices de rendimiento específicos y claves foráneas bien definidas.

---

## 📁 Estructura del Proyecto

```text
AppCortes/
├── BBDD/                     # Recursos y scripts de base de datos
│   ├── schema.sql            # Esquema SQL completo con datos de ejemplo
│   └── bdd.cjs               # Demo script de prueba de encriptación local
├── api/                      # Backend API RESTful en PHP
│   ├── config.php            # Configuración de base de datos y cabeceras CORS
│   ├── login.php             # Control de autenticación de usuarios
│   ├── register.php          # Control de registro de usuarios
│   ├── barbers.php           # Operaciones CRUD para Barberos/Peluqueros
│   ├── citas.php             # Operaciones CRUD para Agenda de Citas
│   ├── clientes.php          # Operaciones CRUD para Clientes
│   ├── cortes.php            # Operaciones CRUD para Catálogo de Cortes
│   └── .htaccess             # Directivas de enrutado e inicio seguro de Apache
├── resources/                # Iconos, imágenes estáticas y recursos globales
│   └── icono.png             # Logo oficial de Ethan Cuts
├── src/                      # Código fuente del cliente de escritorio
│   ├── css/                  # Hojas de estilo y guías visuales
│   ├── js/                   # Controladores e IPC de Electron
│   │   ├── index.js          # Proceso principal de Electron (Main Process)
│   │   ├── preload.js        # Exposición segura de API al renderer (Context Bridge)
│   │   └── encryptor.cjs     # Módulo cifrador criptográfico del cliente
│   └── view/                 # Vistas e interfaces HTML de la aplicación
│       ├── splash.html       # Interfaz de la pantalla de bienvenida
│       ├── login.html        # Pantalla de acceso
│       ├── register.html     # Formulario de registro seguro
│       ├── general.html      # Dashboard principal del sistema
│       ├── citas.html        # Vista y agenda de reservas
│       ├── clientes.html     # Fichero de clientes
│       ├── galeria.html      # Visualizador de cortes
│       ├── configuracion.html # Ajustes y personalización
│       └── styleguide.html   # Guía del sistema de diseño
├── forge.config.js           # Configuración del empaquetador Electron Forge
├── package.json              # Metadatos del proyecto y dependencias de Node
└── README.md                 # Documentación del proyecto (este archivo)
```

---

## ⚙️ Requisitos Previos

Antes de configurar el proyecto, asegúrate de tener instalado:
*   [Node.js](https://nodejs.org/) (Versión 18 o superior recomendada)
*   Un servidor local web con soporte para **PHP 8.x** y **MySQL** (como [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/) o WampServer).

---

## 📦 Instalación y Configuración

### 1. Preparar la Base de Datos
1.  Abre el panel de control de tu servidor local (ej. XAMPP) e inicia los servicios de **Apache** y **MySQL**.
2.  Accede a `phpMyAdmin` (usualmente en `http://localhost/phpmyadmin`).
3.  Crea una nueva base de datos llamada `appcortes`.
4.  Selecciona la base de datos e importa el script SQL ubicado en `BBDD/schema.sql`. Esto creará automáticamente las tablas, índices y datos iniciales de prueba.

### 2. Configurar la API Backend en PHP
1.  Copia o mueve la carpeta `api/` a la carpeta pública de tu servidor local (ej. `C:/xampp/htdocs/appcortes/api/` en Windows).
2.  Si es necesario, edita el archivo `config.php` dentro de tu servidor local para actualizar las credenciales de tu base de datos:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'appcortes');
    define('DB_USER', 'tu_usuario');       // Predeterminado: root
    define('DB_PASS', 'tu_contraseña');    // Predeterminado: vacío ('')
    ```
3.  Asegúrate de que la URL `http://localhost/appcortes/api/config.php` responda correctamente desde tu navegador con un JSON.

### 3. Configurar el Cliente de Escritorio (Electron)
1.  Abre tu terminal en la raíz del proyecto (`AppCortes`).
2.  Instala todas las dependencias requeridas de Node:
    ```bash
    npm install
    ```

---

## 🖥️ Ejecución y Desarrollo

Una vez completados los pasos anteriores, puedes ejecutar la aplicación en modo desarrollo:

```bash
npm start
```

### Comandos Disponibles (Electron Forge)
El proyecto utiliza **Electron Forge** para facilitar el ciclo de desarrollo y empaquetado:

*   `npm start`: Inicia la aplicación en modo desarrollo.
*   `npm run package`: Empaqueta la aplicación para distribución local según la plataforma.
*   `npm run make`: Genera el instalador ejecutable listo para producción (.exe, .deb, .zip, etc.).

---

## 🔐 Seguridad y Criptografía

El módulo `src/js/encryptor.cjs` provee funciones asíncronas seguras para cifrado simétrico en el entorno local del cliente, previniendo fugas de datos sensibles antes de enviarlos o guardarlos.
Puedes validar el funcionamiento del módulo criptográfico ejecutando el archivo de prueba desde tu consola:

```bash
node BBDD/bdd.cjs
```

---

## 👥 Datos de Acceso de Prueba
El esquema de base de datos incluye cuentas iniciales para agilizar el proceso de desarrollo y demostración:

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@appcortes.com` | `admin123` |
| **Peluquero (Ejemplo 1)** | `carlos@appcortes.com` | `admin123` |
| **Peluquero (Ejemplo 2)** | `maria@appcortes.com` | `admin123` |

---

## 📝 Licencia

Este proyecto está bajo la licencia **MIT**. Consulta el archivo de licencia correspondiente para más detalles.

---

Desarrollado con ❤️ por **Daniel Bailo** ([danielbailo2004@gmail.com](mailto:danielbailo2004@gmail.com)).
