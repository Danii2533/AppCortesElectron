<?php
// En este archivo gestiono la API para los Clientes (listar y añadir).
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ---------------------------------------------------------
    // Lógica para Obtener todos los clientes
    // ---------------------------------------------------------
    try {
        $db = getDB();
        // Hago una consulta sencilla para traerme los datos básicos de todos los clientes, ordenados alfabéticamente.
        $stmt = $db->query("SELECT id, nombre, email, telefono, ultima_visita FROM clientes ORDER BY nombre ASC");
        $clientes = $stmt->fetchAll();
        jsonResponse(['success' => true, 'clientes' => $clientes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'POST') {
    // ---------------------------------------------------------
    // Lógica para Registrar un nuevo cliente manualmente
    // ---------------------------------------------------------
    $body = json_decode(file_get_contents('php://input'), true);
    
    // Limpio los datos que me llegan del formulario
    $nombre   = trim($body['nombre'] ?? '');
    $email    = trim($body['email'] ?? '');
    $telefono = trim($body['telefono'] ?? '');

    // El nombre es el único campo estrictamente obligatorio en mi base de datos
    if (!$nombre) {
        jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio'], 400);
    }

    try {
        $db = getDB();
        // Inserto el cliente nuevo. La fecha de creación se pone automática en MySQL gracias a CURRENT_TIMESTAMP.
        $stmt = $db->prepare("INSERT INTO clientes (nombre, email, telefono) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $telefono]);
        
        jsonResponse(['success' => true, 'message' => 'Cliente añadido correctamente'], 201);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
else {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}
