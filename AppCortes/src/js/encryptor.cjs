/*
  encryptor.js
  ----------------------
  Aquí he montado un encriptador y desencriptador AES-GCM. 
  Lo hice así porque necesitaba que fuera robusto y que utilizara la Web Crypto API o el módulo crypto de Node.
*/

// --- Helpers ---
// He creado estas funciones auxiliares para convertir los ArrayBuffers a Base64 y viceversa.
// Esto es súper útil para poder almacenar o transmitir los datos cifrados de forma legible.
function bufToBase64(buf) {
  return Buffer.from(buf).toString('base64');
}

function base64ToBuf(b64) {
  return Buffer.from(b64, 'base64');
}

// Función que uso para generar un vector de inicialización o sal (salt) aleatorio.
// Detecto si estoy en el navegador (crypto.getRandomValues) o en Node.js (require('crypto')).
function randomBytes(len) {
  return crypto.getRandomValues ? crypto.getRandomValues(new Uint8Array(len)).buffer : require('crypto').randomBytes(len).buffer;
}

// Esta función es vital para la seguridad. En lugar de usar la contraseña del usuario directamente para cifrar,
// la paso por PBKDF2 (con 200,000 iteraciones) junto con un salt. Esto hace que sea muy difícil de romper por fuerza bruta.
async function deriveKey(password, salt, iterations = 200000) {
  const enc = new TextEncoder();
  const passKey = await crypto.subtle.importKey(
    'raw',
    enc.encode(password),
    { name: 'PBKDF2' },
    false,
    ['deriveKey']
  );

  return crypto.subtle.deriveKey(
    {
      name: 'PBKDF2',
      salt: salt,
      iterations: iterations,
      hash: 'SHA-256'
    },
    passKey,
    { name: 'AES-GCM', length: 256 },
    false,
    ['encrypt', 'decrypt']
  );
}

// --- Encrypt / Decrypt ---
// Mi función principal para cifrar un texto. Genero un salt (16 bytes) y un IV (12 bytes) aleatorios.
async function encryptText(password, plaintext) {
  const salt = randomBytes(16);
  const iv = randomBytes(12);
  const key = await deriveKey(password, salt);

  const enc = new TextEncoder();
  // Utilizo AES-GCM porque, además de cifrar, me da autenticidad (impide que modifiquen el texto cifrado sin que me dé cuenta).
  const ciphertext = await crypto.subtle.encrypt(
    { name: 'AES-GCM', iv: iv },
    key,
    enc.encode(plaintext)
  );

  // Concateno el salt, el iv y el texto cifrado en un solo buffer. Luego lo convierto a base64 para poder guardarlo.
  const combined = new Uint8Array([...new Uint8Array(salt), ...new Uint8Array(iv), ...new Uint8Array(ciphertext)]);
  return bufToBase64(combined.buffer);
}

// Para desencifrar hago el proceso inverso.
async function decryptText(password, base64Combined) {
  const combinedBuf = base64ToBuf(base64Combined);
  const combined = new Uint8Array(combinedBuf);

  // Extraigo mis piezas usando los tamaños conocidos que definí al cifrar:
  const salt = combined.slice(0, 16).buffer; // 16 bytes para la sal
  const iv = combined.slice(16, 28).buffer;  // 12 bytes para el vector de inicialización
  const ct = combined.slice(28).buffer;      // Todo lo demás es el texto cifrado

  // Derivo la misma llave usando la contraseña que me pasan y la sal que extraje
  const key = await deriveKey(password, salt);
  const decryptedBuf = await crypto.subtle.decrypt(
    { name: 'AES-GCM', iv: iv },
    key,
    ct
  );

  return new TextDecoder().decode(decryptedBuf);
}



// --- Exports ---
// Lo exporto para poder requerirlo desde otras partes de la app en Node.js
module.exports = { encryptText, decryptText };
