// Si es necesario por el instalador de Windows (Squirrel), dejo que se encargue y termino la ejecución aquí.
if (require('electron-squirrel-startup')) return;

const path = require("path");
const { app, BrowserWindow, ipcMain } = require("electron");

// Aquí defino las variables globales para mis ventanas. Así puedo acceder a ellas desde cualquier función en este archivo.
let splashWindow;
let mainWindow;    
let registerWindow;
let generalWindow;

function createWindows() {

  // He configurado la Splash Screen (pantalla de carga).
  // Le he quitado el marco (frame: false) y evito que se redimensione para que parezca una app nativa desde el inicio.
  splashWindow = new BrowserWindow({
    width: 400,
    height: 250,
    frame: false,
    alwaysOnTop: true,
    resizable: false,
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'), 
    webPreferences: {
      preload: path.join(__dirname, 'preload.js') // Conecto el preload para usar ipcRenderer de forma segura
    }
  });
  
  splashWindow.loadFile(path.join(__dirname, "..", "view", "splash.html")); 


  // Esta es la ventana de Login, mi ventana principal.
  // Por defecto la creo oculta (show: false) para mostrarla solo cuando termine el splash.
  mainWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });
  
  mainWindow.loadFile(path.join(__dirname, "..", "view", "login.html"));

  // Si cierran el login, entiendo que quieren salir de la app, así que cierro todo.
  mainWindow.on('closed', () => {
    mainWindow = null;
    app.quit();
  });


  // Ventana de Registro. También oculta de inicio.
  registerWindow = new BrowserWindow({
    title: "Registro",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  registerWindow.loadFile(path.join(__dirname, '..', 'view', 'register.html'));

  registerWindow.on('closed', () => {
    registerWindow = null;
    app.quit();
  });


  // Ventana General (la interfaz de la app una vez logueados). Es más grande para que quepa todo el panel.
  generalWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1500,
    height: 900,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '..', '..', 'resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  generalWindow.loadFile(path.join(__dirname, '..', 'view', 'general.html'));

  generalWindow.on('closed', () => {
    generalWindow = null;
    app.quit();
  });

  // Nota de diseño: Las ventanas modales (añadir corte, cliente, cita) decidí gestionarlas
  // directamente con overlays CSS dentro de cada vista HTML en lugar de abrir nuevas ventanas BrowserWindow.
  // Esto hace que la app se sienta más rápida y fluida.
}

// Función auxiliar que he creado para ocultar todas las ventanas de golpe al cambiar de vista.
function hideAllWindows() {
  const windows = BrowserWindow.getAllWindows();
  windows.forEach(win => {
    if (win && !win.isDestroyed() && win.isVisible()) {
      win.hide();
    }
  });
}

// Cuando Electron esté listo, arranco todo el tinglado.
app.whenReady().then(() => {
  createWindows();
  
  // Solo cuando el splash se haya renderizado en pantalla, empiezo a hacer mis comprobaciones.
  splashWindow.webContents.once('did-finish-load', () => {
    performStartupChecks();
  });
});

// En esta función simulo y compruebo pasos antes de abrir la app, y mando mensajes al splash para que el usuario vea progreso.
async function performStartupChecks() {
  const sendProgress = (text, percent) => {
    if (splashWindow && !splashWindow.isDestroyed()) {
      splashWindow.webContents.send('loading-progress', { text, percent });
    }
  };

  sendProgress('Iniciando entorno seguro...', 10);
  await new Promise(r => setTimeout(r, 500)); // Hago pequeñas pausas para que dé tiempo a leer

  sendProgress('Cargando base de datos locales...', 30);
  await new Promise(r => setTimeout(r, 600));

  sendProgress('Verificando API del servidor...', 60);
  try {
    const { net } = require('electron');
    // Aquí hago un ping a mi servidor PHP local para asegurarme de que el backend esté arriba.
    await new Promise((resolve) => {
      const request = net.request('http://localhost/appcortes/api/config.php');
      request.on('response', () => resolve());
      request.on('error', (err) => {
        console.log('Aviso: No se pudo conectar a la API local', err.message);
        resolve(); // Resuelvo igual para no bloquear el inicio, pero dejo constancia en la consola.
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


// --- EVENTOS DE VENTANAS (IPC MAIN) --- //
// Aquí escucho los mensajes que me llegan desde las vistas a través de ipcRenderer en preload.js.

// Cuando el splash screen me dice que ya terminó de animar su cierre, cierro la ventana y muestro el login.
ipcMain.on('splash-finished', () => {
  splashWindow.close();
  mainWindow.show();
  mainWindow.focus();
});


// Evento para mostrar la ventana de Registro.
ipcMain.on('show-register-window', () => {
  hideAllWindows();
  if (registerWindow) {
    registerWindow.show();
    registerWindow.focus();
  }
});


// Evento para volver al Login.
ipcMain.on('show-login-window', () => {
  hideAllWindows();
  if (mainWindow) {
    mainWindow.show();
    mainWindow.focus();
  }
});


// Evento para abrir el panel general una vez que el usuario se haya autenticado.
ipcMain.on('show-galeria-window', () => {
  hideAllWindows();
  if (generalWindow) {
    generalWindow.show();
    generalWindow.focus();
  }
});