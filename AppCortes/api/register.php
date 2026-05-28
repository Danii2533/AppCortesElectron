<?php
// =============================================
// POST /api/register.php
// Body: { "nombre": "...", "email": "...", "password": "..." }
// Registra siempre como rol 'peluquero' (rol_id = 2)
// =============================================

// Incluyo la configuración para acceder a la base de datos.
require_once __DIR__ . '/config.php';

// Si alguien intenta entrar por GET u otro método que no sea POST, le digo que no se puede.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

// Obtengo el JSON del frontend y limpio los espacios extra con trim().
$body     = json_decode(file_get_contents('php://input'), true);
$nombre   = trim($body['nombre']   ?? '');
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

// Valido que los tres campos estén completos.
if ($nombre === '' || $email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Rellena todos los campos'], 400);
}

// Uso el filtro de PHP para asegurarme de que el email tenga un formato válido (@ y dominio).
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'El correo electrónico no es válido'], 400);
}

// Por seguridad, exijo que la contraseña tenga al menos 6 caracteres.
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

try {
    $db = getDB();

    // Verifico si el email ya existe. No quiero usuarios duplicados.
    $check = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ese correo ya está registrado'], 409);
    }

    // Busco el ID del rol 'peluquero'. Lo hago así en lugar de poner un "2" en duro
    // porque si en el futuro cambio la tabla de roles, no se romperá mi código.
    $rolStmt = $db->prepare("SELECT id FROM roles WHERE nombre = 'peluquero' LIMIT 1");
    $rolStmt->execute();
    $rol = $rolStmt->fetch();
    if (!$rol) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }

    // Genero el hash de la contraseña usando BCRYPT con un coste de 12. 
    // Esto lo hace bastante resistente a ataques.
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Inserto el usuario. Por defecto, le pongo 'activo = 1' para que pueda loguearse de inmediato.
    $stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol_id, activo) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([$nombre, $email, $hash, $rol['id']]);

    jsonResponse(['success' => true, 'message' => 'Registro exitoso. Ya puedes iniciar sesión.']);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}
