const path = require("path");
const { app, BrowserWindow, ipcMain } = require("electron");

let splashWindow;
let mainWindow;        // login
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
    icon: path.join(__dirname, '../resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  splashWindow.loadFile(path.join(__dirname, "./splash.html"));


  // Login Window
  mainWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '../resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  mainWindow.loadFile(path.join(__dirname, "./login.html"));


  // Register Window
  registerWindow = new BrowserWindow({
    title: "Registro",
    width: 1200,
    height: 800,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '../resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  registerWindow.loadFile(path.join(__dirname, './register.html'));

  registerWindow.on('closed', () => {
    registerWindow = null;
  });


  // Galería Window
  generalWindow = new BrowserWindow({
    title: "ETHAN CUTS",
    width: 1500,
    height: 900,
    resizable: false,
    show: false,
    icon: path.join(__dirname, '../resources', 'icono.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.js')
    }
  });

  generalWindow.loadFile(path.join(__dirname, './general.html'));

  generalWindow.on('closed', () => {
    generalWindow = null;
  });

}


// Oculta TODAS las ventanas activas
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
});


// --- EVENTOS DE VENTANAS --- //

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

