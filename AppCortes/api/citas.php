<?php
// En este archivo he implementado toda la API REST para el manejo de las Citas (CRUD).
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ---------------------------------------------------------
    // Lógica para Obtener las citas
    // ---------------------------------------------------------
    try {
        $db = getDB();
        // Hago un JOIN para obtener el nombre del cliente asociado a la cita.
        $stmt = $db->query(
            "SELECT c.id, c.fecha, c.hora, cl.nombre as client, c.notas as service 
             FROM citas c 
             JOIN clientes cl ON c.cliente_id = cl.id"
        );
        $citas = $stmt->fetchAll();
        
        // También me traigo la lista de clientes para poder llenar los autocompletados del frontend.
        $stmtClientes = $db->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
        $clientes = $stmtClientes->fetchAll();
        
        jsonResponse(['success' => true, 'citas' => $citas, 'clientes' => $clientes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'POST') {
    // ---------------------------------------------------------
    // Lógica para Registrar una nueva cita
    // ---------------------------------------------------------
    $body = json_decode(file_get_contents('php://input'), true);
    
    $clientName = trim($body['client'] ?? '');
    $service    = trim($body['service'] ?? '');
    $notes      = trim($body['notes'] ?? '');
    $dateKey    = trim($body['dateKey'] ?? '');
    $hour       = trim($body['hour'] ?? '');

    if (!$clientName || !$dateKey || !$hour) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
    }

    // Formateo la hora (me llega como "9" y la convierto a "09:00:00" para la base de datos).
    $horaFormateada = sprintf("%02d:00:00", $hour);

    // Concateno el servicio y las notas adicionales en un solo campo, ya que en mi tabla uso `notas` para todo.
    $notasCompletas = $service . ($notes ? " - " . $notes : "");

    try {
        $db = getDB();
        // Empiezo una transacción. Así, si algo falla a mitad de camino, deshago todo (rollback).
        $db->beginTransaction();

        // 1. Busco el cliente por su nombre. Si no existe, lo creo al vuelo.
        $stmt = $db->prepare("SELECT id FROM clientes WHERE nombre = ? LIMIT 1");
        $stmt->execute([$clientName]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            $clienteId = $cliente['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO clientes (nombre) VALUES (?)");
            $stmt->execute([$clientName]);
            $clienteId = $db->lastInsertId(); // Guardo el ID del cliente que acabo de crear.
        }

        // 2. Inserto la cita. De momento asocio el peluquero 1 por defecto hasta que amplíe esto.
        $stmt = $db->prepare(
            "INSERT INTO citas (cliente_id, peluquero_id, fecha, hora, estado, notas) 
             VALUES (?, 1, ?, ?, 'pendiente', ?)"
        );
        $stmt->execute([$clienteId, $dateKey, $horaFormateada, $notasCompletas]);
        
        // Confirmo la transacción.
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Cita registrada correctamente'], 201);
    } catch (Exception $e) {
        $db->rollBack(); // Si hay error, cancelo la transacción de la BD.
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'PUT') {
    // ---------------------------------------------------------
    // Lógica para Editar una cita existente
    // ---------------------------------------------------------
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

        // 1. Igual que en el alta, busco o creo al cliente si le han cambiado el nombre en la edición.
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

        // 2. Actualizo la cita con los nuevos datos.
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
    // ---------------------------------------------------------
    // Lógica para Borrar una cita
    // ---------------------------------------------------------
    $body = json_decode(file_get_contents('php://input'), true);
    $citaId = intval($body['id'] ?? 0);

    if (!$citaId) {
        jsonResponse(['success' => false, 'message' => 'ID de cita requerido'], 400);
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM citas WHERE id = ?");
        $stmt->execute([$citaId]);

        // Verifico que realmente se borró algo (por si envían un ID que no existe).
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
