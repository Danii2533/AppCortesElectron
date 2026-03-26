<?php
// =============================================
// POST /api/register.php
// Body: { "nombre": "...", "email": "...", "password": "..." }
// Registra siempre como rol 'peluquero' (rol_id = 2)
// =============================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

$body     = json_decode(file_get_contents('php://input'), true);
$nombre   = trim($body['nombre']   ?? '');
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

if ($nombre === '' || $email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Rellena todos los campos'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'El correo electrónico no es válido'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

try {
    $db = getDB();

    // Verificar email duplicado
    $check = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ese correo ya está registrado'], 409);
    }

    // Obtener rol_id de 'peluquero' dinámicamente (escalable)
    $rolStmt = $db->prepare("SELECT id FROM roles WHERE nombre = 'peluquero' LIMIT 1");
    $rolStmt->execute();
    $rol = $rolStmt->fetch();
    if (!$rol) {
        jsonResponse(['success' => false, 'message' => 'Rol "peluquero" no encontrado en la BD'], 500);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol_id, activo) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([$nombre, $email, $hash, $rol['id']]);

    jsonResponse(['success' => true, 'message' => 'Registro exitoso. Ya puedes iniciar sesión.']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()], 500);
}
