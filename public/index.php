<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configurações básicas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Conexão com o banco de dados
$dbConfig = require __DIR__ . '/../config/database.php';
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    
    // Execução das migrations
    try {
        $migrationRunner = new \migrations\MigrationRunner($db);
        $migrationRunner->run();
    } catch (\Exception $e) {
        error_log("Migration error: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// Carrega todas as rotas
$allRoutes = require __DIR__ . '/../config/routes.php';
$apiRoutes = $allRoutes['api'] ?? [];
$frontendRoutes = $allRoutes['frontend'] ?? [];

// Processa a requisição
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '') {
    $requestUri = '/';
}

// Verifica se é uma rota API primeiro
if (strpos($requestUri, '/api/') === 0) {
    // Remove o prefixo /api/ da URI para processar
    $apiUri = substr($requestUri, 4);
    if ($apiUri === '') {
        $apiUri = '/';
    }
    
    // Processa como API
    require __DIR__ . '/api_routes.php';
    exit;
}

// Verifica se é uma rota frontend
$frontendRouteFound = false;
foreach ($frontendRoutes as $route => $config) {
    // Substitui parâmetros dinâmicos por regex
    $pattern = str_replace('/:id', '/(\d+)', $route);
    $pattern = str_replace('/:code', '/([a-zA-Z0-9-]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $requestUri, $matches)) {
        $frontendRouteFound = true;
        
        // Extrai parâmetros da URL
        $params = [];
        if (count($matches) > 1) {
            // Identifica qual parâmetro foi capturado
            if (strpos($route, ':id') !== false) {
                $params['id'] = $matches[1];
            } elseif (strpos($route, ':code') !== false) {
                $params['code'] = $matches[1];
            }
        }
        
        // Define variáveis globais para a view
        $pageTitle = $config['title'] ?? 'Mini ERP';
        $activePage = $config['active'] ?? '';
        $breadcrumbs = $config['breadcrumbs'] ?? null;
        
        // Renderiza a página
        $viewPath = __DIR__ . '/../views/' . $config['view'];
        if (file_exists($viewPath)) {
            // Inclui a view (que já deve incluir header e footer)
            include $viewPath;
        } else {
            // Página 404
            http_response_code(404);
            $pageTitle = 'Página não encontrada';
            $activePage = '404';
            
            require __DIR__ . '/../views/layout/header.php';
            echo '<div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <h1 class="display-1 text-muted">404</h1>
                            <h2>Página não encontrada</h2>
                            <p class="lead">A página que você está procurando não existe.</p>
                            <a href="/" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Voltar ao início
                            </a>
                        </div>
                    </div>
                  </div>';
            require __DIR__ . '/../views/layout/footer.php';
        }
        
        break;
    }
}

// Se não encontrou rota frontend, processa como API (fallback)
if (!$frontendRouteFound) {
    require __DIR__ . '/api_routes.php';
}
