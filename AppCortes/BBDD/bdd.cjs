const { encryptText, decryptText } = require('../src/encryptor.cjs');

(async () => {
  const password = 'clave123';
  const mensaje = 'Dato sensible';

  const encriptado = await encryptText(password, mensaje);
  console.log('Encriptado:', encriptado);

  const desencriptado = await decryptText(password, encriptado);
  console.log('Desencriptado:', desencriptado);
})();
