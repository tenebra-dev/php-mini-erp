<?php
namespace services;

use \PDO;
use \Exception;
use \PDOException;
use dto\product\ProductUpdateDTO;
use dto\product\ProductCreateDTO;
use interfaces\ProductServiceInterface;

/**
 * Classe de serviço para gerenciar produtos
 */
class ProductService implements ProductServiceInterface {
    /**
     * @var \PDO
     */
    private $db;
    
    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    /**
     * Retorna todos os produtos
     *
     * @return array
     */
    public function getAllProducts() {
        try {
            $stmt = $this->db->query("SELECT * FROM products");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage());
            throw new Exception('Failed to fetch products', 500);
        }
    }

    /**
     * Retorna um produto pelo ID
     *
     * @param int $id
     * @return array|null
     */
    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product by ID: " . $e->getMessage());
            throw new Exception('Failed to fetch product', 500);
        }
    }

    /**
     * Retorna todas as variações de um produto
     *
     * @param int $productId
     * @return array
     */
    public function getProductVariations($productId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM product_variations WHERE product_id = ?");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product variations: " . $e->getMessage());
            throw new Exception('Failed to fetch product variations', 500);
        }
    }

    /**
     * Retorna o estoque de um produto
     *
     * @param int $productId
     * @return array
     */
    public function getProductStock($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.quantity, v.variation_name, v.variation_value 
                FROM stock s
                LEFT JOIN product_variations v ON s.variation_id = v.id
                WHERE s.product_id = ?
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product stock: " . $e->getMessage());
            throw new Exception('Failed to fetch product stock', 500);
        }
    }

    /**
     * Retorna uma variação de produto pelo ID
     *
     * @param int $variationId
     * @return array|null
     */
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

    /**
     * Cria um novo produto
     *
     * @param \dto\product\ProductCreateDTO $dto
     * @return int ID do produto criado
     * @throws Exception
     */
    public function createProduct(ProductCreateDTO $dto) {
        $this->db->beginTransaction();
        try {
            // Insere o produto
            $stmt = $this->db->prepare("
                INSERT INTO products (name, price, description) 
                VALUES (:name, :price, :description)
            ");
            $stmt->execute([
                'name' => trim($dto->name),
                'price' => (float)$dto->price,
                'description' => $dto->description ?? null
            ]);
            $productId = $this->db->lastInsertId();

            // Processa variações e estoque
            if (!empty($dto->variations)) {
                foreach ($dto->variations as $variation) {
                    $variationId = $this->createVariation($productId, $variation);
                    $this->createStock($productId, $variationId, $variation->quantity ?? 0);
                }
            } else {
                $this->createStock($productId, null, $dto->quantity ?? 0);
            }

            $this->db->commit();
            return $productId;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Product creation error: " . $e->getMessage());
            throw new Exception('Failed to create product', 500);
        }
    }

    /**
     * Atualiza um produto
     *
     * @param int $id ID do produto
     * @param \dto\product\ProductUpdateDTO $dto Dados para atualização
     * @return bool
     * @throws Exception
     */
    public function updateProduct($id, ProductUpdateDTO $dto) {
        $fields = [];
        $params = [];
        if ($dto->name !== null) {
            $fields[] = "name = ?";
            $params[] = $dto->name;
        }
        if ($dto->price !== null) {
            $fields[] = "price = ?";
            $params[] = $dto->price;
        }
        if ($dto->description !== null) {
            $fields[] = "description = ?";
            $params[] = $dto->description;
        }
        if ($dto->quantity !== null) {
            $fields[] = "quantity = ?";
            $params[] = $dto->quantity;
        }
        // Se quiser atualizar variações, trate separadamente (normalmente é outra tabela)
        if (!$fields) throw new \Exception("No fields to update", 400);
        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Atualiza variações se enviadas
        if (is_array($dto->variations)) {
            // Remove variações e estoque antigos
            $this->db->prepare("DELETE FROM stock WHERE product_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM product_variations WHERE product_id = ?")->execute([$id]);

            // Insere as novas variações e estoque
            foreach ($dto->variations as $variation) {
                $variationId = $this->createVariation($id, $variation);
                $this->createStock($id, $variationId, $variation['quantity'] ?? 0);
            }
        }

        return true;
    }

    /**
     * Deleta um produto pelo ID
     *
     * @param int $id ID do produto
     * @return bool
     * @throws Exception
     */
    public function deleteProduct($id) {
        try {
            // Remove estoque e variações primeiro
            $this->db->prepare("DELETE FROM stock WHERE product_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM product_variations WHERE product_id = ?")->execute([$id]);
            // Agora remove o produto
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

    /**
     * Diminui o estoque de uma variação
     *
     * @param int $variationId
     * @param int $quantity
     * @throws Exception
     */
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

    /**
     * Aumenta o estoque de uma variação
     *
     * @param int $variationId ID da variação
     * @param int $quantity Quantidade a ser adicionada
     * @throws Exception Se a variação não for encontrada
     */
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

    /**
     * Cria uma variação de produto
     *
     * @param int $productId ID do produto
     * @param object|array $variationData Dados da variação
     * @return int ID da variação criada
     */
    private function createVariation($productId, $variationData) {
        $stmt = $this->db->prepare("
            INSERT INTO product_variations 
            (product_id, variation_name, variation_value) 
            VALUES (:product_id, :name, :value)
        ");
        $stmt->execute([
            'product_id' => $productId,
            'name' => is_array($variationData) ? $variationData['name'] : $variationData->name,
            'value' => is_array($variationData) ? $variationData['value'] : $variationData->value
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Cria um registro de estoque para uma variação
     *
     * @param int $productId ID do produto
     * @param int|null $variationId ID da variação (null se for o produto principal)
     * @param int $quantity Quantidade em estoque
     */
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

    /**
     * Retorna produtos paginados com filtros
     *
     * @param int $page Número da página
     * @param int $perPage Número de itens por página
     * @param array $filters Filtros de busca
     * @return array Produtos paginados e informações de paginação
     */
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

    /**
     * Cria um produto com variações
     *
     * @param array $data Dados do produto e variações
     * @return int ID do produto criado
     * @throws Exception
     */
    public function createProductWithVariations(array $data)
    {
        // Cria o produto principal
        $dto = new \dto\product\ProductCreateDTO($data);
        $productId = $this->createProduct($dto);

        // Cria variações, se houver
        if (!empty($data['variations'])) {
            foreach ($data['variations'] as $variation) {
                $this->createVariation($productId, $variation);
            }
        }
        return $productId;
    }
}
