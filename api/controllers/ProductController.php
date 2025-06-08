<?php
namespace controllers;

use services\ProductService;
use \Exception;

class ProductController {
    private $productService;
    
    public function __construct(\PDO $db) {
        $this->productService = new ProductService($db);
    }
    
    public function handleProducts($params, $data) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $this->getAllProducts();
            case 'POST':
                return $this->createProduct($data);
            default:
                throw new \Exception('Method not allowed', 405);
        }
    }
    
    public function handleProduct($params, $data) {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = $params['id'] ?? null;
        
        if (!$id) {
            throw new \Exception('Product ID is required', 400);
        }
        
        switch ($method) {
            case 'GET':
                return $this->getProduct($id);
            case 'PUT':
                return $this->updateProduct($id, $data);
            case 'DELETE':
                return $this->deleteProduct($id);
            default:
                throw new \Exception('Method not allowed', 405);
        }
    }
    
    private function getAllProducts() {
        $products = $this->productService->getAllProducts();
        return [
            'success' => true,
            'data' => $products
        ];
    }
    
    private function getProduct($id) {
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
    }
    
    private function createProduct($data) {
        // Validação
        if (empty($data['name']) || !isset($data['price'])) {
            throw new \Exception('Name and price are required', 400);
        }
        
        $productId = $this->productService->createProductWithVariations($data);
        
        return [
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ];
    }
    
    private function updateProduct($id, $data) {
        // Validação
        if (empty($data['name']) || !isset($data['price'])) {
            throw new \Exception('Name and price are required', 400);
        }
        
        $this->productService->updateProduct($id, $data);
        
        return [
            'success' => true,
            'message' => 'Product updated successfully'
        ];
    }
    
    private function deleteProduct($id) {
        $this->productService->deleteProduct($id);
        
        return [
            'success' => true,
            'message' => 'Product deleted successfully'
        ];
    }
}
