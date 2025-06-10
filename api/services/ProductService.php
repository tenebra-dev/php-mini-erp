<?php
namespace services;

use \PDO;
use \Exception;
use \PDOException;

class ProductService {
    private $db;
    
    public function __construct(\PDO $db) {
        $this->db = $db;
    }
    
    public function getAllProducts() {
        $stmt = $this->db->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVariationById($variationId) {
    $stmt = $this->db->prepare("
        SELECT pv.*, s.quantity 
        FROM product_variations pv
        LEFT JOIN stock s ON pv.id = s.variation_id
        WHERE pv.id = ?
    ");
    $stmt->execute([$variationId]);
    $variation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$variation) {
        return null;
    }
    
    return [
        'id' => $variation['id'],
        'product_id' => $variation['product_id'],
        'variation_name' => $variation['variation_name'],
        'variation_value' => $variation['variation_value'],
        'quantity' => $variation['quantity']
        ];
    }

    public function decreaseStock($variationId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE stock 
            SET quantity = quantity - :quantity 
            WHERE variation_id = :variation_id 
            AND quantity >= :quantity
        ");
        $stmt->execute([
            'quantity' => $quantity,
            'variation_id' => $variationId
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Insufficient stock or variation not found', 400);
        }
    }

    public function increaseStock($variationId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE stock 
            SET quantity = quantity + :quantity 
            WHERE variation_id = :variation_id
        ");
        $stmt->execute([
            'quantity' => $quantity,
            'variation_id' => $variationId
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Variation not found', 404);
        }
    }

    public function getProductVariations($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_variations WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductStock($productId) {
        $stmt = $this->db->prepare("
            SELECT s.quantity, v.variation_name, v.variation_value 
            FROM stock s
            LEFT JOIN product_variations v ON s.variation_id = v.id
            WHERE s.product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createProductWithVariations($data) {
        $this->db->beginTransaction();
        
        try {
            // Insere o produto
            $stmt = $this->db->prepare("
                INSERT INTO products (name, price, description) 
                VALUES (:name, :price, :description)
            ");
            $stmt->execute([
                'name' => trim($data['name']),
                'price' => (float)$data['price'],
                'description' => $data['description'] ?? null
            ]);
            
            $productId = $this->db->lastInsertId();
            
            // Processa variações e estoque
            if (!empty($data['variations'])) {
                foreach ($data['variations'] as $variation) {
                    $variationId = $this->createVariation($productId, $variation);
                    $this->createStock($productId, $variationId, $variation['quantity'] ?? 0);
                }
            } else {
                $this->createStock($productId, null, $data['quantity'] ?? 0);
            }
            
            $this->db->commit();
            return $productId;
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Product creation error: " . $e->getMessage());
            throw new Exception('Failed to create product', 500);
        }
    }
    
    private function createVariation($productId, $variationData) {
        $stmt = $this->db->prepare("
            INSERT INTO product_variations 
            (product_id, variation_name, variation_value) 
            VALUES (:product_id, :name, :value)
        ");
        $stmt->execute([
            'product_id' => $productId,
            'name' => $variationData['name'],
            'value' => $variationData['value']
        ]);
        return $this->db->lastInsertId();
    }
    
    private function createStock($productId, $variationId, $quantity) {
        $stmt = $this->db->prepare("
            INSERT INTO stock 
            (product_id, variation_id, quantity) 
            VALUES (:product_id, :variation_id, :quantity)
        ");
        $stmt->execute([
            'product_id' => $productId,
            'variation_id' => $variationId,
            'quantity' => $quantity
        ]);
    }
    
    public function updateProduct($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE products 
                SET name = :name, 
                    price = :price, 
                    description = :description
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'name' => trim($data['name']),
                'price' => (float)$data['price'],
                'description' => $data['description'] ?? null
            ]);
            
            // Verifica se o produto existe ANTES de lançar erro por rowCount
            if ($stmt->rowCount() === 0) {
                // Checa se o produto existe
                $check = $this->db->prepare("SELECT id FROM products WHERE id = ?");
                $check->execute([$id]);
                if (!$check->fetch()) {
                    throw new Exception('Product not found', 404);
                }
                // Se existe, apenas não houve alteração nos dados (não é erro)
            }
            
            // Atualiza variações e estoque se fornecidos
            if (isset($data['variations'])) {
                $this->updateVariations($id, $data['variations']);
            }
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            throw new Exception('Failed to update product', 500);
        }
    }
    
    private function updateVariations($productId, $variations) {
        // Remove variações existentes
        $this->db->prepare("DELETE FROM product_variations WHERE product_id = ?")
             ->execute([$productId]);
        
        // Remove estoque existente
        $this->db->prepare("DELETE FROM stock WHERE product_id = ?")
             ->execute([$productId]);
        
        // Adiciona novas variações
        foreach ($variations as $variation) {
            $variationId = $this->createVariation($productId, $variation);
            $this->createStock($productId, $variationId, $variation['quantity'] ?? 0);
        }
    }
    
    public function deleteProduct($id) {
        try {
            // As foreign keys com ON DELETE CASCADE cuidam das tabelas relacionadas
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Product not found', 404);
            }
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            throw new Exception('Failed to delete product', 500);
        }
    }

    public function getPaginatedProducts($page = 1, $perPage = 10, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = 'name LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['category']) && $filters['category'] !== '') {
            $where[] = 'category = :category';
            $params['category'] = $filters['category'];
        }
        if (isset($filters['has_stock']) && $filters['has_stock'] !== '') {
            $where[] = $filters['has_stock'] ? 'total_stock > 0' : 'total_stock = 0';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Conta total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM products $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Busca paginada
        $stmt = $this->db->prepare("SELECT * FROM products $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Adiciona estoque total
        foreach ($products as &$product) {
            $product['total_stock'] = $this->getProductStock($product['id']);
        }

        return [
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ];
    }
}
