<?php
// =============================================
// AppCortes - Configuración de base de datos
// Aquí he centralizado toda la configuración para que la app se conectue a la base de datos MySQL.
// =============================================

// He definido las constantes con los datos de acceso a mi servidor local de base de datos.
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'appcortes');
define('DB_USER', 'root');       // Pongo 'root' por defecto en local, pero esto lo cambiaré en producción.
define('DB_PASS', '');           // Lo dejo vacío en local porque XAMPP por defecto no usa contraseña.

// He añadido estas cabeceras CORS. Esto es vital para que Electron pueda hacer peticiones a mi API PHP
// sin que el navegador bloquee las llamadas por políticas de seguridad cruzada (Cross-Origin).
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Aquí manejo las peticiones OPTIONS, que son comprobaciones previas (preflight) que hacen los navegadores.
// Si no devuelvo 200 aquí, las peticiones POST desde Electron podrían fallar.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// He creado esta función `getDB()` usando el patrón Singleton para la conexión PDO.
// Así me aseguro de que solo abro una conexión a la base de datos por cada petición HTTP, ahorrando recursos.
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Configuro para que lance excepciones si hay errores SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Me devuelve los resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Uso sentencias preparadas reales por seguridad contra Inyección SQL
        ]);
    }
    return $pdo;
}

// He creado esta función auxiliar para no repetir código cada vez que necesito devolver una respuesta JSON.
// Le paso el array de datos y opcionalmente el código HTTP, lo transforma y corta la ejecución.
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
