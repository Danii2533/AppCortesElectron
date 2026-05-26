<?php
// =============================================
// GET  /api/barbers.php   → lista peluqueros (solo admin)
// POST /api/barbers.php   → activa/desactiva peluquero (solo admin)
//   Body POST: { "id": 5, "activo": 0 }
//   Header requerido: X-Admin-Email
// =============================================
require_once __DIR__ . '/config.php';

function checkAdmin(): void {
    $email = $_SERVER['HTTP_X_ADMIN_EMAIL'] ?? '';
    if ($email === '') {
        jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
    }
    $db   = getDB();
    // JOIN con roles para verificar por nombre, no por id hardcodeado
    $stmt = $db->prepare(
        'SELECT u.id FROM usuarios u
         INNER JOIN roles r ON r.id = u.rol_id
         WHERE u.email = ? AND u.activo = 1 AND r.nombre = "admin"
         LIMIT 1'
    );
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
    }
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // ----------------------------------------
        // Lista todos los peluqueros con su rol
        // ----------------------------------------
        checkAdmin();
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT u.id, u.nombre, u.email, u.activo, u.creado_en, r.nombre AS rol
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE r.nombre = "peluquero"
             ORDER BY u.nombre ASC'
        );
        $stmt->execute();
        jsonResponse(['success' => true, 'peluqueros' => $stmt->fetchAll()]);

    } elseif ($method === 'POST') {
        // ----------------------------------------
        // Activar / Desactivar un peluquero
        // ----------------------------------------
        checkAdmin();
        $body   = json_decode(file_get_contents('php://input'), true);
        $id     = (int)($body['id']     ?? 0);
        $activo = (int)($body['activo'] ?? 0);

        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'ID inválido'], 400);
        }

        $db = getDB();

        // Proteger: no se puede desactivar a un admin
        $check = $db->prepare(
            'SELECT r.nombre AS rol FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = ? LIMIT 1'
        );
        $check->execute([$id]);
        $target = $check->fetch();

        if (!$target) {
            jsonResponse(['success' => false, 'message' => 'No se pudo completar la operación'], 404);
        }
        if ($target['rol'] === 'admin') {
            jsonResponse(['success' => false, 'message' => 'Operación no permitida'], 403);
        }

        $stmt = $db->prepare('UPDATE usuarios SET activo = ? WHERE id = ?');
        $stmt->execute([$activo ? 1 : 0, $id]);

        jsonResponse([
            'success' => true,
            'message' => $activo ? 'Peluquero activado' : 'Peluquero desactivado',
        ]);

    } else {
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
    }

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}
