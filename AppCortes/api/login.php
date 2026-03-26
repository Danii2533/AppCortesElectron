<?php
// =============================================
// POST /api/login.php
// Body: { "email": "...", "password": "..." }
// Response: { success, id, nombre, rol }
// =============================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$body     = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

if ($email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Rellena todos los campos'], 400);
}

try {
    $db = getDB();

    // JOIN con roles para devolver el nombre del rol
    $stmt = $db->prepare(
        'SELECT u.id, u.nombre, u.email, u.password_hash, u.activo, r.nombre AS rol
         FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos'], 401);
    }

    if ((int)$user['activo'] === 0) {
        jsonResponse(['success' => false, 'message' => 'Tu cuenta está desactivada. Contacta al administrador.'], 403);
    }

    jsonResponse([
        'success' => true,
        'id'      => $user['id'],
        'nombre'  => $user['nombre'],
        'email'   => $user['email'],
        'rol'     => $user['rol'],   // 'admin' o 'peluquero' (desde tabla roles)
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()], 500);
}
