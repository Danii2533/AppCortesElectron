<?php
require_once 'config.php';

// Definir la ruta absoluta de la carpeta de imágenes en el proyecto de Electron
$uploadDir = 'C:\\Users\\D BAILO\\ElectronApp\\AppCortesElectron\\AppCortes\\resources\\cortes_de_pelo\\';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener todos los cortes
    try {
        $db = getDB();
        $stmt = $db->query("SELECT id, nombre, tags, imagen_path FROM cortes ORDER BY id DESC");
        $cortes = $stmt->fetchAll();
        jsonResponse(['success' => true, 'cortes' => $cortes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al obtener cortes: ' . $e->getMessage()], 500);
    }
} 
elseif ($method === 'POST') {
    // Subir un nuevo corte
    $body = json_decode(file_get_contents('php://input'), true);
    
    $nombre = trim($body['nombre'] ?? '');
    $tags   = trim($body['tags'] ?? '');
    $base64 = $body['image_base64'] ?? '';
    
    if (!$nombre || !$base64) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos (nombre o imagen)'], 400);
    }

    try {
        // 1. Decodificar la imagen Base64
        // Formato esperado: "data:image/jpeg;base64,iVBORw0KGgo..."
        $imgParts = explode(';base64,', $base64);
        if (count($imgParts) != 2) {
            jsonResponse(['success' => false, 'message' => 'Formato de imagen inválido'], 400);
        }
        
        $imageTypeAux = explode('image/', $imgParts[0]);
        $imageType = $imageTypeAux[1]; // ej. 'png' o 'jpeg'
        $imageBase64 = base64_decode($imgParts[1]);
        
        // 2. Crear nombre de archivo único
        $fileName = uniqid() . '.' . $imageType;
        $filePath = $uploadDir . $fileName;

        // 3. Asegurar que el directorio existe y guardar el archivo
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (file_put_contents($filePath, $imageBase64) === false) {
             jsonResponse(['success' => false, 'message' => 'Error escribiendo el archivo en el disco'], 500);
        }

        // 4. Registrar en base de datos
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO cortes (nombre, tags, imagen_path, peluquero_id) VALUES (?, ?, ?, 1)");
        $stmt->execute([$nombre, $tags, $fileName]);
        
        jsonResponse(['success' => true, 'message' => 'Corte añadido correctamente', 'fileName' => $fileName], 201);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al guardar corte: ' . $e->getMessage()], 500);
    }
} 
else {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}
