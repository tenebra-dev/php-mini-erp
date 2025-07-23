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
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($products as $productData) {
                $productData['variations'] = $this->getProductVariations($productData['id']);
                $productData['stock'] = $this->getProductStock($productData['id']);
                $result[] = new \models\Product($productData);
            }
            return $result;
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
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) return null;
            // Carrega variações e estoque
            $data['variations'] = $this->getProductVariations($id);
            $data['stock'] = $this->getProductStock($id);
            return new \models\Product($data);
        } catch (\PDOException $e) {
            error_log("Error fetching product by ID: " . $e->getMessage());
            throw new \Exception('Failed to fetch product', 500);
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
            // 1. Lida com o upload da imagem
            $imagePath = null;
            if (!empty($dto->image) && is_array($dto->image) && $dto->image['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($dto->image);
            }

            // 2. Insere o produto no banco de dados
            $stmt = $this->db->prepare("
                INSERT INTO products (name, sku, price, description, image) 
                VALUES (:name, :sku, :price, :description, :image)
            ");
            $stmt->execute([
                'name' => trim($dto->name),
                'sku' => $dto->sku,
                'price' => (float)$dto->price,
                'description' => $dto->description,
                'image' => $imagePath
            ]);
            $productId = $this->db->lastInsertId();

            // 3. Processa variações e estoque
            if (!empty($dto->variations)) {
                foreach ($dto->variations as $variation) {
                    $variationId = $this->createVariation($productId, $variation);
                    $this->createStock($productId, $variationId, $variation['quantity'] ?? 0);
                }
            } else {
                // Estoque para produto simples
                $this->createStock($productId, null, $dto->quantity ?? 0);
            }

            $this->db->commit();
            return $productId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            // Se o upload da imagem foi feito, mas a transação falhou, remove o arquivo
            if (isset($imagePath)) {
                $this->deleteImageFile($imagePath);
            }
            error_log("Product creation error: " . $e->getMessage());
            throw new Exception('Failed to create product: ' . $e->getMessage(), 500);
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
        $this->db->beginTransaction();
        try {
            $product = $this->getProductById($id);
            if (!$product) {
                throw new Exception("Product not found", 404);
            }

            $fields = [];
            $params = [];

            if ($dto->name !== null) { $fields[] = "name = ?"; $params[] = $dto->name; }
            if ($dto->sku !== null) { $fields[] = "sku = ?"; $params[] = $dto->sku; }
            if ($dto->price !== null) { $fields[] = "price = ?"; $params[] = $dto->price; }
            if ($dto->description !== null) { $fields[] = "description = ?"; $params[] = $dto->description; }

            // Lida com a atualização da imagem
            $newImagePath = null;
            if (!empty($dto->image) && is_array($dto->image) && $dto->image['error'] === UPLOAD_ERR_OK) {
                $newImagePath = $this->handleImageUpload($dto->image);
                $fields[] = "image = ?";
                $params[] = $newImagePath;
            }

            if ($fields) {
                $params[] = $id;
                $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            // Se uma nova imagem foi salva, remove a antiga
            if ($newImagePath && $product->image) {
                $this->deleteImageFile($product->image);
            }

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

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Se uma nova imagem foi salva, mas a transação falhou, remove o arquivo
            if (isset($newImagePath)) {
                $this->deleteImageFile($newImagePath);
            }
            error_log("Product update error: " . $e->getMessage());
            throw new Exception('Failed to update product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Deleta um produto
     *
     * @param int $id ID do produto
     * @return bool
     * @throws Exception
     */
    public function deleteProduct($id) {
        $this->db->beginTransaction();
        try {
            // Pega o produto para obter o caminho da imagem antes de deletar
            $product = $this->getProductById($id);
            if (!$product) {
                throw new Exception('Product not found', 404);
            }

            // Remove estoque e variações primeiro
            $this->db->prepare("DELETE FROM stock WHERE product_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM product_variations WHERE product_id = ?")->execute([$id]);
            
            // Agora remove o produto
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);

            // Se o produto foi deletado do DB, remove a imagem do servidor
            if ($stmt->rowCount() > 0 && $product->image) {
                $this->deleteImageFile($product->image);
            }
            
            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Delete error: " . $e->getMessage());
            // Verifica se é erro de integridade referencial
            if (strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
                throw new Exception('Não é possível excluir: produto já vinculado a pedidos.', 400);
            }
            throw new Exception('Failed to delete product', 500);
        }
    }

    /**
     * Diminui o estoque de um produto ou variação
     *
     * @param int $productId
     * @param int|null $variationId
     * @param int $quantity
     * @throws Exception
     */
    public function decreaseStock($productId, $variationId, $quantity) {
        $sql = "UPDATE stock SET quantity = quantity - :quantity WHERE product_id = :product_id";
        $params = [
            'quantity' => $quantity,
            'product_id' => $productId
        ];

        if ($variationId) {
            $sql .= " AND variation_id = :variation_id";
            $params['variation_id'] = $variationId;
        } else {
            $sql .= " AND variation_id IS NULL";
        }
        
        $sql .= " AND quantity >= :quantity";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Insufficient stock or item not found', 400);
        }
    }

    /**
     * Aumenta o estoque de um produto ou variação
     *
     * @param int $productId ID do produto
     * @param int|null $variationId ID da variação
     * @param int $quantity Quantidade a ser adicionada
     * @throws Exception Se a variação não for encontrada
     */
    public function increaseStock($productId, $variationId, $quantity) {
        $sql = "UPDATE stock SET quantity = quantity + :quantity WHERE product_id = :product_id";
        $params = [
            'quantity' => $quantity,
            'product_id' => $productId
        ];

        if ($variationId) {
            $sql .= " AND variation_id = :variation_id";
            $params['variation_id'] = $variationId;
        } else {
            $sql .= " AND variation_id IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Item not found in stock to increase quantity', 404);
        }
    }

    /**
     * Verifica o estoque de um produto ou variação.
     *
     * @param int $productId ID do produto
     * @param int|null $variationId ID da variação (opcional)
     * @param int $quantity Quantidade desejada
     * @return bool
     * @throws \Exception Se não houver estoque suficiente
     */
    public function checkStock($productId, $variationId, $quantity) {
        $sql = "SELECT quantity FROM stock WHERE product_id = :product_id";
        $params = ['product_id' => $productId];

        if ($variationId) {
            $sql .= " AND variation_id = :variation_id";
            $params['variation_id'] = $variationId;
        } else {
            // Se não houver variationId, assumimos que é um produto simples
            // cujo estoque é registrado com variation_id = NULL
            $sql .= " AND variation_id IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stockQuantity = $stmt->fetchColumn();

        if ($stockQuantity === false) {
            // Lança exceção se não encontrar o registro de estoque
            throw new \Exception('Stock information not found for this product/variation.', 404);
        }

        if ((int)$stockQuantity < (int)$quantity) {
            // Lança exceção se o estoque for insuficiente
            throw new \Exception('Insufficient stock for the requested quantity.', 400);
        }

        return true;
    }

    /**
     * Lida com o upload de um arquivo de imagem.
     *
     * @param array $file O array do arquivo de `$_FILES`.
     * @return string O caminho relativo da imagem salva.
     * @throws Exception Se o upload falhar.
     */
    private function handleImageUpload(array $file): string {
        $uploadDir = __DIR__ . '/../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validação básica do arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.', 400);
        }

        // Gera um nome de arquivo único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('product_', true) . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image.', 500);
        }

        // Retorna o caminho relativo para ser salvo no banco
        return '/uploads/products/' . $fileName;
    }

    /**
     * Deleta um arquivo de imagem do servidor.
     *
     * @param string|null $imagePath O caminho relativo da imagem.
     */
    private function deleteImageFile(?string $imagePath): void {
        if (!$imagePath) {
            return;
        }
        $fullPath = __DIR__ . '/../../public' . $imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
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

        // Adiciona estoque total e stock_quantity
        $result = [];
        foreach ($products as $product) {
            $product['stock'] = $this->getProductStock($product['id']);
            $product['stock_quantity'] = array_sum(array_column($product['stock'], 'quantity'));
            $result[] = new \models\Product($product);
        }

        return [
            'data' => $result,
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

    /**
     * Atualiza o estoque de uma variação
     *
     * @param int $variationId
     * @param int $quantity
     * @throws Exception
     */
    public function updateVariationStock($variationId, $quantity) {
        // Primeiro tenta atualizar
        $stmt = $this->db->prepare("UPDATE stock SET quantity = :quantity WHERE variation_id = :variation_id");
        $stmt->execute([
            'quantity' => $quantity,
            'variation_id' => $variationId
        ]);
        if ($stmt->rowCount() === 0) {
            // Verifica se a variação existe
            $variation = $this->getVariationById($variationId);
            if (!$variation) {
                throw new \Exception('Variação não encontrada', 404);
            }
            // Cria o registro de estoque para essa variação
            $this->createStock($variation['product_id'], $variationId, $quantity);
        }
    }

    /**
     * Atualiza o estoque de um produto
     *
     * @param int $productId
     * @param int $quantity
     * @throws Exception
     */
    public function updateProductStock($productId, $quantity) {
        $stmt = $this->db->prepare("UPDATE stock SET quantity = :quantity WHERE product_id = :product_id AND variation_id IS NULL");
        $stmt->execute([
            'quantity' => $quantity,
            'product_id' => $productId
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \Exception('Produto simples não encontrado ou estoque não atualizado', 404);
        }
    }
}
