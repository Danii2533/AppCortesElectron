<?php
// =============================================
// POST /api/login.php
// Body: { "email": "...", "password": "..." }
// Response: { success, id, nombre, rol }
// =============================================

// Primero incluyo mi archivo de configuración para tener acceso a la base de datos y a mis funciones útiles.
require_once __DIR__ . '/config.php';

// Me aseguro de que el endpoint solo responda a peticiones POST, ya que estoy enviando credenciales.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

// Recojo y decodifico el JSON que me llega desde Electron.
$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

// Compruebo que no me hayan enviado campos vacíos antes de molestar a la base de datos.
if ($email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Rellena todos los campos'], 400);
}

try {
    // Obtengo la conexión a la base de datos.
    $db = getDB();

    // Hago un JOIN con la tabla roles. Así en una sola consulta me traigo los datos del usuario y también 
    // el nombre de su rol (admin o peluquero), que me hará falta para decidir qué interfaz mostrarle en Electron.
    $stmt = $db->prepare(
        'SELECT u.id, u.nombre, u.email, u.password_hash, u.activo, r.nombre AS rol
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Si la consulta no devuelve nada, el correo no existe en mi sistema.
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    // Aquí utilizo password_verify. Esto es fundamental por seguridad, ya que nunca guardo las contraseñas 
    // en texto plano, sino un hash generado con password_hash() en el registro.
    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    // Compruebo si el usuario sigue activo (por si algún admin lo ha desactivado o despedido).
    if ((int)$user['activo'] === 0) {
        jsonResponse(['success' => false, 'message' => 'Tu cuenta está desactivada. Contacta al administrador.'], 403);
    }

    // Si todo va bien, devuelvo los datos necesarios para montar la sesión en el frontend.
    // Ojo: nunca devuelvo el hash de la contraseña aquí.
    jsonResponse([
        'success' => true,
        'id'      => $user['id'],
        'nombre'  => $user['nombre'],
        'email'   => $user['email'],
        'rol'     => $user['rol'],   // 'admin' o 'peluquero'
    ]);

} catch (Exception $e) {
    // Si la base de datos falla por cualquier motivo, capturo el error para que la app no pete feamente.
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}
