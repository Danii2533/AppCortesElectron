<?php
// En este archivo manejo la galería de cortes (subir imágenes y listarlas).
require_once 'config.php';

// Definir la ruta absoluta de la carpeta de imágenes en mi proyecto de Electron.
// Lo hago así porque necesito guardar los archivos físicos en una carpeta a la que Electron pueda acceder fácilmente.
$uploadDir = 'C:\\Users\\D BAILO\\ElectronApp\\AppCortesElectron\\AppCortes\\resources\\cortes_de_pelo\\';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ---------------------------------------------------------
    // Lógica para Obtener todos los cortes para la galería
    // ---------------------------------------------------------
    try {
        $db = getDB();
        // Ordeno por ID descendente para que los últimos cortes añadidos salgan primero.
        $stmt = $db->query("SELECT id, nombre, tags, imagen_path FROM cortes ORDER BY id DESC");
        $cortes = $stmt->fetchAll();
        jsonResponse(['success' => true, 'cortes' => $cortes], 200);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
elseif ($method === 'POST') {
    // ---------------------------------------------------------
    // Lógica para Subir un nuevo corte (Imagen + Datos)
    // ---------------------------------------------------------
    $body = json_decode(file_get_contents('php://input'), true);
    
    $nombre = trim($body['nombre'] ?? '');
    $tags   = trim($body['tags'] ?? '');
    // La imagen me llega en formato Base64 desde el frontend (es más fácil de enviar en un JSON).
    $base64 = $body['image_base64'] ?? '';
    
    if (!$nombre || !$base64) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos (nombre o imagen)'], 400);
    }

    try {
        // 1. Decodificar la imagen Base64
        // El formato esperado es: "data:image/jpeg;base64,iVBORw0KGgo..."
        // Separo la cabecera del contenido real usando explode.
        $imgParts = explode(';base64,', $base64);
        if (count($imgParts) != 2) {
            jsonResponse(['success' => false, 'message' => 'Formato de imagen inválido'], 400);
        }
        
        // Extraigo el tipo de imagen (png, jpeg, etc.) para guardarla con la extensión correcta.
        $imageTypeAux = explode('image/', $imgParts[0]);
        $imageType = $imageTypeAux[1]; 
        $imageBase64 = base64_decode($imgParts[1]); // Aquí ya tengo los bytes reales de la imagen.
        
        // 2. Crear nombre de archivo único
        // Uso uniqid() para que no se sobreescriban fotos si dos personas suben un archivo llamado igual.
        $fileName = uniqid() . '.' . $imageType;
        $filePath = $uploadDir . $fileName;

        // 3. Asegurar que el directorio existe y guardar el archivo
        // Si la carpeta 'cortes_de_pelo' no existe, la creo.
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Escribo el archivo físico en el disco duro.
        if (file_put_contents($filePath, $imageBase64) === false) {
             jsonResponse(['success' => false, 'message' => 'Error escribiendo el archivo en el disco'], 500);
        }

        // 4. Registrar en base de datos
        // Guardo la referencia (el nombre del archivo) en la BD, no la imagen entera.
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO cortes (nombre, tags, imagen_path, peluquero_id) VALUES (?, ?, ?, 1)");
        $stmt->execute([$nombre, $tags, $fileName]);
        
        jsonResponse(['success' => true, 'message' => 'Corte añadido correctamente', 'fileName' => $fileName], 201);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
    }
} 
else {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}
