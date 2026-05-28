// Este script es el "Preload". Lo uso como puente seguro entre el proceso principal de Node (index.js) y mis vistas HTML (Renderer).
// No quiero que el HTML tenga acceso directo a Node.js por seguridad, así que solo expongo lo que necesito a través de 'contextBridge'.
const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
  // Estas funciones mandan mensajes (eventos) al proceso principal para que cambie de ventana.
  finishSplash: () => ipcRenderer.send('splash-finished'),
  showLoginWindow: () => ipcRenderer.send('show-login-window'),
  showRegisterWindow: () => ipcRenderer.send('show-register-window'),
  showGaleriaWindow: () => ipcRenderer.send('show-galeria-window'),
  
  // Con esto, las vistas pueden escuchar eventos que vienen desde el proceso principal,
  // como el progreso de carga de mi splash screen.
  onLoadingProgress: (callback) => ipcRenderer.on('loading-progress', (_event, value) => callback(value))
});
