<?php
// Configurações de CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Verifica requisição OPTIONS (pré-voo CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Processa o corpo da requisição
$requestData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $requestData = json_decode($json, true) ?? [];
    } else {
        $requestData = $_POST;
    }
}

// Roteamento da API
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove o prefixo /api/ se existir
if (strpos($requestUri, '/api/') === 0) {
    $requestUri = substr($requestUri, 4);
}

$requestUri = trim($requestUri, '/');
$segments = $requestUri ? explode('/', $requestUri) : [];

$routeFound = false;

// Log para debug (remover em produção)
error_log("API Request - Method: $requestMethod, URI: $requestUri, Segments: " . implode(',', $segments));

foreach ($apiRoutes as $route => $config) {
    $routeSegments = explode('/', trim($route, '/'));
    
    // Log para debug (remover em produção)
    error_log("Checking route: $route against " . implode('/', $segments));
    
    if (count($routeSegments) !== count($segments)) {
        continue;
    }
    
    $params = [];
    $match = true;
    
    foreach ($routeSegments as $index => $segment) {
        if (strpos($segment, ':') === 0) {
            // Parâmetro dinâmico
            $paramName = substr($segment, 1);
            $params[$paramName] = $segments[$index] ?? null;
        } elseif ($segment !== $segments[$index]) {
            $match = false;
            break;
        }
    }
    
    if ($match && in_array($requestMethod, $config['methods'])) {
        $routeFound = true;
        
        $controllerName = $config['controller'];
        $methodName = $config['method'];
        
        try {
            // Verifica se a classe do controller existe
            if (!class_exists($controllerName)) {
                throw new Exception("Controller class '$controllerName' not found");
            }
            
            $controller = new $controllerName($db);
            
            // Verifica se o método existe
            if (!method_exists($controller, $methodName)) {
                throw new Exception("Method '$methodName' not found in controller '$controllerName'");
            }
            
            $response = $controller->$methodName($params, array_merge($_REQUEST, $requestData));
            
            // Se a resposta já foi enviada (headers já enviados), não processa mais
            if (headers_sent()) {
                exit;
            }
            
            if (is_array($response) || is_object($response)) {
                http_response_code(200);
                echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                echo $response;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        }
        
        break;
    }
}

if (!$routeFound) {
    http_response_code(404);
    echo json_encode([
        'error' => 'API endpoint not found',
        'method' => $requestMethod,
        'uri' => $requestUri,
        'available_routes' => array_keys($apiRoutes)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
