<?php
header("Content-Type: application/json");
require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../api/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$request = $_SERVER['REQUEST_METHOD'];
$url = $_GET['url'] ?? '';

try {
    $db = new PDO(
        'mysql:host=db;dbname=testdb',
        'testuser',
        'testpass'
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ("$request $url") {
        case 'GET ':
        case 'GET /':
            echo json_encode(['message' => 'API funcionando!']);
            break;

        case 'GET users':
            $stmt = $db->query("SELECT * FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
