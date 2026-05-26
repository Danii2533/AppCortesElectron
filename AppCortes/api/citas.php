<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener citas
    try {
        $db = getDB();
        $stmt = $db->query(
            "SELECT c.id, c.fecha, c.hora, cl.nombre as client, c.notas as service 
             FROM citas c 
             JOIN clientes cl ON c.cliente_id = cl.id"
        );
        $citas = $stmt->fetchAll();
        
        $stmtClientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
        $clientes = $stmtClientes->fetchAll();
        
        jsonResponse(['success' => true, 'citas' => $citas, 'clientes' => $clientes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'POST') {
    // Registrar nueva cita
    $body = json_decode(file_get_contents('php://input'), true);
    
    $clientName = trim($body['client'] ?? '');
    $service    = trim($body['service'] ?? '');
    $notes      = trim($body['notes'] ?? '');
    $dateKey    = trim($body['dateKey'] ?? '');
    $hour       = trim($body['hour'] ?? '');

    if (!$clientName || !$dateKey || !$hour) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
    }

    // Formatear hora (de "9" a "09:00:00")
    $horaFormateada = sprintf("%02d:00:00", $hour);

    // Concatenar servicio y notas para guardar en el campo notas
    $notasCompletas = $service . ($notes ? " - " . $notes : "");

    try {
        $db = getDB();
        $db->beginTransaction();

        // 1. Buscar o crear cliente
        $stmt = $db->prepare("SELECT id FROM clientes WHERE nombre = ? LIMIT 1");
        $stmt->execute([$clientName]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            $clienteId = $cliente['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO clientes (nombre) VALUES (?)");
            $stmt->execute([$clientName]);
            $clienteId = $db->lastInsertId();
        }

        // 2. Insertar cita (usamos peluquero_id = 1 por defecto por ahora)
        $stmt = $db->prepare(
            "INSERT INTO citas (cliente_id, peluquero_id, fecha, hora, estado, notas) 
             VALUES (?, 1, ?, ?, 'pendiente', ?)"
        );
        $stmt->execute([$clienteId, $dateKey, $horaFormateada, $notasCompletas]);
        
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Cita registrada correctamente'], 201);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'PUT') {
    // Editar cita existente
    $body = json_decode(file_get_contents('php://input'), true);
    
    $citaId     = intval($body['id'] ?? 0);
    $clientName = trim($body['client'] ?? '');
    $service    = trim($body['service'] ?? '');
    $notes      = trim($body['notes'] ?? '');
    $dateKey    = trim($body['dateKey'] ?? '');
    $hour       = trim($body['hour'] ?? '');

    if (!$citaId || !$clientName || !$dateKey || !$hour) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
    }

    $horaFormateada = sprintf("%02d:00:00", $hour);
    $notasCompletas = $service . ($notes ? " - " . $notes : "");

    try {
        $db = getDB();
        $db->beginTransaction();

        // 1. Buscar o crear cliente
        $stmt = $db->prepare("SELECT id FROM clientes WHERE nombre = ? LIMIT 1");
        $stmt->execute([$clientName]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            $clienteId = $cliente['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO clientes (nombre) VALUES (?)");
            $stmt->execute([$clientName]);
            $clienteId = $db->lastInsertId();
        }

        // 2. Actualizar cita
        $stmt = $db->prepare(
            "UPDATE citas SET cliente_id = ?, fecha = ?, hora = ?, notas = ? WHERE id = ?"
        );
        $stmt->execute([$clienteId, $dateKey, $horaFormateada, $notasCompletas, $citaId]);
        
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Cita actualizada correctamente'], 200);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
}
elseif ($method === 'DELETE') {
    // Borrar cita
    $body = json_decode(file_get_contents('php://input'), true);
    $citaId = intval($body['id'] ?? 0);

    if (!$citaId) {
        jsonResponse(['success' => false, 'message' => 'ID de cita requerido'], 400);
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM citas WHERE id = ?");
        $stmt->execute([$citaId]);

        if ($stmt->rowCount() > 0) {
            jsonResponse(['success' => true, 'message' => 'Cita eliminada correctamente'], 200);
        } else {
            jsonResponse(['success' => false, 'message' => 'No se pudo completar la operación'], 404);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
}
else {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}
