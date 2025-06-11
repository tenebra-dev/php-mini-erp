<?php
namespace controllers;

use services\ProductService;
use \Exception;
use dto\product\ProductUpdateDTO;
use dto\product\ProductCreateDTO;

class ProductController {
    private $productService;
    
    public function __construct(\PDO $db) {
        $this->productService = new ProductService($db);
    }
    
    /**
     * Handles requests for products.
     *
     * @param array $params URL parameters
     * @param array $data Request body data
     * @return array Response data
     */
    public function __call($name, $arguments) {
        error_log("[ProductController] Method $name not found");
        return [
            'success' => false,
            'message' => 'Method not found',
            'code' => 404
        ];
    }

    public function handleProducts($params, $data) {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        try {
            switch ($method) {
                case 'GET':
                    return $this->getAllProducts();
                case 'POST':
                    // POST /api/products
                    $dto = new ProductCreateDTO($data);
                    if (!$dto->isValid()) {
                        throw new \Exception('Dados inválidos para criação de produto', 400);
                    }
                    $this->productService->createProduct($dto);
                    return ['success' => true, 'message' => 'Produto criado com sucesso'];
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[ProductController][handleProducts] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Handles requests for a specific product.
     *
     * @param array $params URL parameters (should contain 'id')
     * @param array $data Request body data
     * @return array Response data
     */
    public function handleProduct($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method === 'POST' && isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Product ID is required', 400);
            }

            if ($method === 'PUT' && empty($data)) {
                $data = $_POST;
                if (!empty($_FILES)) {
                    $data['image'] = $_FILES['image'];
                }
            }

            switch ($method) {
                case 'GET':
                    return $this->getProduct($id);
                case 'PUT':
                    // PUT /api/products/:id
                    $dto = new ProductUpdateDTO($data);
                    if (!$dto->isValid()) {
                        throw new \Exception('Dados inválidos para atualização de produto', 400);
                    }
                    $this->productService->updateProduct($id, $dto);
                    return ['success' => true, 'message' => 'Produto atualizado'];
                case 'DELETE':
                    return $this->deleteProduct($id);
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[ProductController][handleProduct] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Retrieves all products with pagination and filters.
     *
     * @return array Response data
     */
    private function getAllProducts() {
        try {
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
            $filters = [
                'search' => $_GET['search'] ?? '',
                'category' => $_GET['category'] ?? '',
                'has_stock' => $_GET['has_stock'] ?? ''
            ];
            $result = $this->productService->getPaginatedProducts($page, $perPage, $filters);
            return [
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ];
        } catch (\Exception $e) {
            error_log("[ProductController][getAllProducts] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Retrieves a single product by ID.
     *
     * @param int $id Product ID
     * @return array Response data
     */
    private function getProduct($id) {
        try {
            $product = $this->productService->getProductById($id);
            if (!$product) {
                throw new \Exception('Product not found', 404);
            }
            $product['variations'] = $this->productService->getProductVariations($id);
            $product['stock'] = $this->productService->getProductStock($id);
            return [
                'success' => true,
                'data' => $product
            ];
        } catch (\Exception $e) {
            error_log("[ProductController][getProduct] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao buscar produto: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Creates a new product with variations.
     *
     * @param array $data Product data
     * @return array Response data
     */
    private function createProduct($data) {
        try {
            if (empty($data['name']) || !isset($data['price'])) {
                throw new \Exception('Name and price are required', 400);
            }
            $productId = $this->productService->createProductWithVariations($data);
            return [
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $productId
            ];
        } catch (\Exception $e) {
            error_log("[ProductController][createProduct] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao criar produto: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Updates an existing product.
     *
     * @param int $id Product ID
     * @param array $data Updated product data
     * @return array Response data
     */
    private function updateProduct($id, $data) {
        try {
            if (empty($data['name']) || !isset($data['price'])) {
                throw new \Exception('Name and price are required', 400);
            }
            $this->productService->updateProduct($id, $data);
            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];
        } catch (\Exception $e) {
            error_log("[ProductController][updateProduct] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Deletes a product by ID.
     *
     * @param int $id Product ID
     * @return array Response data
     */
    private function deleteProduct($id) {
        try {
            $this->productService->deleteProduct($id);
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        } catch (\Exception $e) {
            error_log("[ProductController][deleteProduct] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao deletar produto: ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
}
