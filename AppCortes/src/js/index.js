const path = require("path");
const { app, BrowserWindow, ipcMain } = require("electron");

let splashWindow;
let mainWindow;    
let registerWindow;
let generalWindow;

function createWindows() {

  // Splash Screen
  splashWindow = new BrowserWindow({
    width: 400,
    height: 250,
    frame: false,
    alwaysOnTop: true,
    resizable: false,
    // [Ruta de recurso] resources está en /src/resources
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'), 
    webPreferences: {
      // preload.js está en la misma carpeta (/js)
      preload: path.join(__dirname, 'preload.js')
    }
  });
  
  // ¡Ruta corregida a /src/views/splash.html!
  splashWindow.loadFile(path.join(__dirname, "..", "view", "splash.html")); 


  // Login Window
  mainWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    // [Ruta de recurso] resources está en /src/resources
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });
  
  // ¡Ruta corregida a /src/views/login.html!
  mainWindow.loadFile(path.join(__dirname, "..", "view", "login.html"));

  mainWindow.on('closed', () => {
    mainWindow = null;
    app.quit();
  });


  // Register Window
  registerWindow = new BrowserWindow({
    title: "Registro",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    // [Ruta de recurso] resources está en /src/resources
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  // ¡Ruta corregida a /src/views/register.html!
  registerWindow.loadFile(path.join(__dirname, '..', 'view', 'register.html'));

  registerWindow.on('closed', () => {
    registerWindow = null;
    app.quit();
  });


  // VentanaGenera y sub ventanas
  generalWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1500,
    height: 900,
    resizable: false,
    show: false,
    // [Ruta de recurso] resources está en /src/resources
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  // ¡Ruta corregida a /src/views/general.html!
  generalWindow.loadFile(path.join(__dirname, '..', 'view', 'general.html'));

  generalWindow.on('closed', () => {
    generalWindow = null;
    app.quit();
  });

  // Las ventanas modales (añadir corte, cliente, cita) se gestionan
  // directamente con overlays CSS dentro de cada vista HTML.
}

// ... (Resto del código sin cambios) ...

function hideAllWindows() {
  const windows = BrowserWindow.getAllWindows();
  windows.forEach(win => {
    if (win && !win.isDestroyed() && win.isVisible()) {
      win.hide();
    }
  });
}

app.whenReady().then(() => {
  createWindows();
  
  splashWindow.webContents.once('did-finish-load', () => {
    performStartupChecks();
  });
});

async function performStartupChecks() {
  const sendProgress = (text, percent) => {
    if (splashWindow && !splashWindow.isDestroyed()) {
      splashWindow.webContents.send('loading-progress', { text, percent });
    }
  };

  sendProgress('Iniciando entorno seguro...', 10);
  await new Promise(r => setTimeout(r, 500));

  sendProgress('Cargando base de datos locales...', 30);
  await new Promise(r => setTimeout(r, 600));

  sendProgress('Verificando API del servidor...', 60);
  try {
    const { net } = require('electron');
    await new Promise((resolve) => {
      const request = net.request('http://localhost/appcortes/api/config.php');
      request.on('response', () => resolve());
      request.on('error', (err) => {
        console.log('Aviso: No se pudo conectar a la API local', err.message);
        resolve(); 
      });
      request.end();
    });
  } catch (e) {
    console.error(e);
  }
  await new Promise(r => setTimeout(r, 400));

  sendProgress('Preparando interfaz principal...', 90);
  await new Promise(r => setTimeout(r, 500));

  sendProgress('Carga completada', 100);
}


// --- EVENTOS DE VENTANAS (sin cambios) --- //

// Fin del splash → mostrar login
ipcMain.on('splash-finished', () => {
  splashWindow.close();
  mainWindow.show();
  mainWindow.focus();
});


// Mostrar ventana Registro
ipcMain.on('show-register-window', () => {
  hideAllWindows();
  if (registerWindow) {
    registerWindow.show();
    registerWindow.focus();
  }
});


// Mostrar Login
ipcMain.on('show-login-window', () => {
  hideAllWindows();
  if (mainWindow) {
    mainWindow.show();
    mainWindow.focus();
  }
});


// Mostrar Galería de Cortes
ipcMain.on('show-galeria-window', () => {
  hideAllWindows();
  if (generalWindow) {
    generalWindow.show();
    generalWindow.focus();
  }
});