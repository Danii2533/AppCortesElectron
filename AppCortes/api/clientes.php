<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener todos los clientes
    try {
        $db = getDB();
        $stmt = $db->query("SELECT id, nombre, email, telefono, ultima_visita FROM clientes ORDER BY nombre ASC");
        $clientes = $stmt->fetchAll();
        jsonResponse(['success' => true, 'clientes' => $clientes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'POST') {
    // Registrar nuevo cliente
    $body = json_decode(file_get_contents('php://input'), true);
    
    $nombre   = trim($body['nombre'] ?? '');
    $email    = trim($body['email'] ?? '');
    $telefono = trim($body['telefono'] ?? '');

    if (!$nombre) {
        jsonResponse(['success' => false, 'message' => 'El nombre es obligatorio'], 400);
    }

    try {
        $db = getDB();
        // Insertar cliente nuevo (la fecha de creacion se pone automática)
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
