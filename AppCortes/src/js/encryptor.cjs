/*
  encryptor.js
  ----------------------
  Encriptador y desencriptador con prueba en terminal.
  Compatible con Node.js 19+ y navegadores modernos.
*/

// --- Helpers ---
function bufToBase64(buf) {
  return Buffer.from(buf).toString('base64');
}

function base64ToBuf(b64) {
  return Buffer.from(b64, 'base64');
}

function randomBytes(len) {
  return crypto.getRandomValues ? crypto.getRandomValues(new Uint8Array(len)).buffer : require('crypto').randomBytes(len).buffer;
}

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
async function encryptText(password, plaintext) {
  const salt = randomBytes(16);
  const iv = randomBytes(12);
  const key = await deriveKey(password, salt);

  const enc = new TextEncoder();
  const ciphertext = await crypto.subtle.encrypt(
    { name: 'AES-GCM', iv: iv },
    key,
    enc.encode(plaintext)
  );

  const combined = new Uint8Array([...new Uint8Array(salt), ...new Uint8Array(iv), ...new Uint8Array(ciphertext)]);
  return bufToBase64(combined.buffer);
}

async function decryptText(password, base64Combined) {
  const combinedBuf = base64ToBuf(base64Combined);
  const combined = new Uint8Array(combinedBuf);

  const salt = combined.slice(0, 16).buffer;
  const iv = combined.slice(16, 28).buffer;
  const ct = combined.slice(28).buffer;

  const key = await deriveKey(password, salt);
  const decryptedBuf = await crypto.subtle.decrypt(
    { name: 'AES-GCM', iv: iv },
    key,
    ct
  );

  return new TextDecoder().decode(decryptedBuf);
}



// --- Exports (opcional para import) ---
module.exports = { encryptText, decryptText };
