const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
  finishSplash: () => ipcRenderer.send('splash-finished'),
  showLoginWindow: () => ipcRenderer.send('show-login-window'),
  showRegisterWindow: () => ipcRenderer.send('show-register-window'),
  showGaleriaWindow: () => ipcRenderer.send('show-galeria-window')
});
