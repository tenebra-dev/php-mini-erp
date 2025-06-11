<?php
namespace services;

use services\ProductService;
use services\CouponService;
use services\EmailService;
use \PDO;
use \Exception;
use \PDOException;
use dto\order\OrderUpdateDTO;
use dto\order\OrderCreateDTO;
use interfaces\OrderServiceInterface;

/**
 * Classe OrderService
 * Implementa a lógica de negócios para gerenciar pedidos, carrinho, checkout e webhooks.
 */
class OrderService implements OrderServiceInterface {
    // ================ Atributos ================
    /**
     * @var \PDO Conexão com o banco de dados
     */
    private $db;
    private $productService;
    private $couponService;
    private $emailService;
    /**
     * OrderService constructor.
     * @param \PDO $db Conexão com o banco de dados
     */
    public function __construct(\PDO $db) {
        $this->db = $db;
        $this->productService = new ProductService($db);
        $this->couponService = new CouponService($db);
        $this->emailService = new EmailService();
    }

    // ================ Métodos do Carrinho ================

    /**
     * Adiciona um item ao carrinho
     * @param array $data Dados do produto e quantidade
     * @return array Resultado da adição
     */
    public function addToCart($data) {
        try {
            if (empty($data['product_id']) || empty($data['quantity'])) {
                throw new \Exception('Product ID and quantity are required', 400);
            }

            // Se o produto tem variação, exija variation_id
            $product = $this->productService->getProductById($data['product_id']);
            $hasVariations = !empty($this->productService->getProductVariations($data['product_id']));

            if ($hasVariations && empty($data['variation_id'])) {
                throw new \Exception('Variation ID is required for this product', 400);
            }

            // Se não tem variação, defina variation_id como null
            $variationId = $hasVariations ? $data['variation_id'] : null;

            // Verifica estoque
            if ($hasVariations) {
                $variation = $this->productService->getVariationById($variationId);
                if (!$variation || $variation['quantity'] < $data['quantity']) {
                    throw new \Exception('Product or variation not available', 400);
                }
            } else {
                // Busca estoque do produto sem variação
                $stockList = $this->productService->getProductStock($data['product_id']);
                $stock = $stockList[0]['quantity'] ?? 0;
                if ($stock < $data['quantity']) {
                    throw new \Exception('Insufficient stock', 400);
                }
            }

            // Adiciona ao carrinho
            $itemKey = $data['product_id'] . '_' . ($variationId ?? '0');
            if (isset($_SESSION['cart']['items'][$itemKey])) {
                $_SESSION['cart']['items'][$itemKey]['quantity'] += $data['quantity'];
            } else {
                $_SESSION['cart']['items'][$itemKey] = [
                    'product_id' => $data['product_id'],
                    'variation_id' => $variationId,
                    'quantity' => $data['quantity'],
                    'unit_price' => $product['price'],
                    'product_name' => $product['name'],
                    'variation_name' => $hasVariations && isset($variation) ? ($variation['variation_name'] . ': ' . $variation['variation_value']) : null
                ];
            }

            $this->calculateCartTotals();

            return [
                'success' => true,
                'message' => 'Item added to cart',
                'cart' => $_SESSION['cart']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'cart' => $_SESSION['cart']
            ];
        }
    }

    /**
     * Aplica um cupom de desconto ao carrinho
     * @param string $couponCode Código do cupom
     * @return array Resultado da aplicação do cupom
     */
    public function applyCoupon($couponCode) {
        try {
            // Valida se há itens no carrinho
            if (empty($_SESSION['cart']['items'])) {
                throw new \Exception('Cannot apply coupon to empty cart', 400);
            }

            // Valida o cupom através do CouponService
            $coupon = $this->couponService->validateCoupon(
                $couponCode,
                $_SESSION['cart']['subtotal']
            );

            // Aplica o cupom
            $_SESSION['cart']['coupon'] = $couponCode;
            $this->calculateCartTotals();

            return [
                'success' => true,
                'message' => 'Coupon applied successfully',
                'cart' => $_SESSION['cart']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'cart' => $_SESSION['cart']
            ];
        }
    }

    /**
     * Remove o cupom do carrinho
     * @return array Resultado da remoção
     */
    public function removeCoupon() {
        $_SESSION['cart']['coupon'] = null;
        $this->calculateCartTotals();

        return [
            'success' => true,
            'message' => 'Coupon removed successfully',
            'cart' => $_SESSION['cart']
        ];
    }

    /**
     * Retorna o carrinho atual
     * @return array Detalhes do carrinho
     */
    public function getCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],
                'coupon' => null,
                'shipping' => 0,
                'subtotal' => 0,
                'total' => 0
            ];
        }

        return [
            'success' => true,
            'cart' => $_SESSION['cart']
        ];
    }

    /**
     * Remove um item do carrinho
     * @param string $itemKey Chave do item no formato "product_id_variation_id"
     * @return array Resultado da remoção
     */
    public function removeItemFromCart($itemKey) {
        if (isset($_SESSION['cart']['items'][$itemKey])) {
            unset($_SESSION['cart']['items'][$itemKey]);
            $this->calculateCartTotals();

            return [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => $_SESSION['cart']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Item not found in cart',
                'cart' => $_SESSION['cart']
            ];
        }
    }

    /**
     * Limpa o carrinho
     * @return array Resultado da limpeza
     */
    public function clearCart() {
        $_SESSION['cart'] = [
            'items' => [],
            'coupon' => null,
            'shipping' => 0,
            'subtotal' => 0,
            'total' => 0
        ];

        return [
            'success' => true,
            'message' => 'Cart cleared'
        ];
    }

    /**
     * Calcula os totais do carrinho (subtotal, frete, desconto e total)
     */
    private function calculateCartTotals() {
        $subtotal = 0;

        foreach ($_SESSION['cart']['items'] as $item) {
            $subtotal += $item['unit_price'] * $item['quantity'];
        }

        $_SESSION['cart']['subtotal'] = $subtotal;

        // Calcula frete conforme as regras do teste
        $_SESSION['cart']['shipping'] = $this->calculateShipping($subtotal);

        // Aplica cupom se existir
        if (!empty($_SESSION['cart']['coupon'])) {
            $coupon = $this->couponService->getCouponByCode($_SESSION['cart']['coupon']);
            if ($coupon && $coupon['is_valid'] && $subtotal >= $coupon['min_value']) {
                $_SESSION['cart']['discount'] = $coupon['discount_value'];
            } else {
                $_SESSION['cart']['coupon'] = null;
                $_SESSION['cart']['discount'] = 0;
            }
        } else {
            $_SESSION['cart']['discount'] = 0;
        }

        $_SESSION['cart']['total'] = $_SESSION['cart']['subtotal'] +
            $_SESSION['cart']['shipping'] -
            $_SESSION['cart']['discount'];
    }

    // ================ Métodos do Checkout ================

    /**
     * Processa o checkout e cria um pedido
     * @param array $data Dados do cliente e do pedido
     * @return array Resultado do processamento
     */
    public function processCheckout($data) {
        try {
            // Validação básica
            $requiredFields = ['customer_name', 'customer_email', 'customer_cep', 'customer_address'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Field $field is required", 400);
                }
            }

            if (empty($_SESSION['cart']['items'])) {
                throw new \Exception('Cart is empty', 400);
            }

            // Verifica CEP via ViaCEP
            $cepInfo = $this->getCepInfo($data['customer_cep']);
            if (!$cepInfo || isset($cepInfo['erro'])) {
                throw new \Exception('Invalid CEP', 400);
            }

            // Completa os dados do endereço com informações do ViaCEP
            $orderData = [
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_cep' => $data['customer_cep'],
                'customer_address' => $data['customer_address'],
                'customer_neighborhood' => $cepInfo['bairro'] ?? '',
                'customer_city' => $cepInfo['localidade'] ?? '',
                'customer_state' => $cepInfo['uf'] ?? '',
                'items' => $_SESSION['cart']['items'],
                'subtotal' => $_SESSION['cart']['subtotal'],
                'shipping' => $_SESSION['cart']['shipping'],
                'discount' => $_SESSION['cart']['discount'] ?? 0,
                'total' => $_SESSION['cart']['total'],
                'coupon_code' => $_SESSION['cart']['coupon'] ?? null
            ];

            // Cria o pedido
            $orderId = $this->createOrder($orderData);

            // Atualiza estoque através do ProductService
            foreach ($_SESSION['cart']['items'] as $item) {
                $this->productService->decreaseStock(
                    $item['variation_id'],
                    $item['quantity']
                );
            }

            // Limpa o carrinho
            $this->clearCart();

            // Envia email através do EmailService
            $this->emailService->sendOrderConfirmation($orderId, $orderData);

            return [
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $orderId,
                'data' => $orderData
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ================ Métodos do Webhook ================

    /**
     * Processa o webhook de atualização de pedidos
     * @param array $data Dados recebidos do webhook
     * @return array Resultado do processamento
     */
    public function processWebhook($data) {
        try {
            if (empty($data['order_id']) || empty($data['status'])) {
                throw new \Exception('Order ID and status are required', 400);
            }

            $orderId = $data['order_id'];
            $status = strtolower($data['status']);

            if ($status === 'canceled') {
                // Remove o pedido
                $this->deleteOrder($orderId);

                // Restaura o estoque através do ProductService
                $orderItems = $this->getOrderItems($orderId);
                foreach ($orderItems as $item) {
                    $this->productService->increaseStock(
                        $item['variation_id'],
                        $item['quantity']
                    );
                }

                return [
                    'success' => true,
                    'message' => 'Order canceled and removed'
                ];
            } else {
                // Atualiza o status
                $this->updateOrderStatus($orderId, $status);

                return [
                    'success' => true,
                    'message' => 'Order status updated'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ================ Métodos de Criação e Gestão de Pedidos ================

    /**
     * Cria um pedido com base nos dados fornecidos
     * @param OrderCreateDTO $dto
     * @return int ID do pedido criado
     * @throws Exception
     */
    public function createOrder(OrderCreateDTO $dto) {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    customer_name, customer_email, customer_cep, customer_address,
                    customer_complement, customer_neighborhood, customer_city, customer_state,
                    subtotal, shipping, discount, total, status, coupon_code
                ) VALUES (
                    :customer_name, :customer_email, :customer_cep, :customer_address,
                    :customer_complement, :customer_neighborhood, :customer_city, :customer_state,
                    :subtotal, :shipping, :discount, :total, :status, :coupon_code
                )
            ");

            $stmt->execute([
                'customer_name' => $dto->customer_name,
                'customer_email' => $dto->customer_email,
                'customer_cep' => $dto->customer_cep,
                'customer_address' => $dto->customer_address,
                'customer_complement' => $dto->customer_complement,
                'customer_neighborhood' => $dto->customer_neighborhood,
                'customer_city' => $dto->customer_city,
                'customer_state' => $dto->customer_state,
                'subtotal' => $dto->subtotal,
                'shipping' => $dto->shipping,
                'discount' => $dto->discount,
                'total' => $dto->total,
                'status' => 'pending',
                'coupon_code' => $dto->coupon_code
            ]);

            $orderId = $this->db->lastInsertId();

            // Adiciona itens do pedido
            $this->addOrderItems($orderId, $dto->items);

            $this->db->commit();

            return $orderId;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            throw new Exception('Failed to create order', 500);
        }
    }

    /**
     * Calcula os totais do pedido (subtotal, frete, desconto e total)
     * @param array $items Itens do pedido
     * @param string|null $couponCode Código do cupom, se houver
     * @return array Totais calculados
     */
    private function calculateTotals(array $items, ?string $couponCode) {
        $subtotal = 0;

        // Calcula subtotal
        foreach ($items as $item) {
            $subtotal += $item['unit_price'] * $item['quantity'];
        }

        // Calcula frete
        $shipping = $this->calculateShipping($subtotal);

        // Aplica cupom através do CouponService
        $discount = 0;
        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, $subtotal);
                $discount = $coupon['discount_value'];
            } catch (\Exception $e) {
                // Se cupom inválido, desconto = 0
                $discount = 0;
            }
        }

        $total = $subtotal + $shipping - $discount;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total
        ];
    }

    /**
     * Calcula o valor do frete com base no subtotal
     * @param float $subtotal Subtotal do pedido
     * @return float Valor do frete
     */
    private function calculateShipping(float $subtotal) {
        if ($subtotal >= 200) {
            return 0; // Frete grátis
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }
        return 20;
    }

    /**
     * Adiciona itens ao pedido
     * @param int $orderId ID do pedido
     * @param array $items Itens do pedido
     */
    private function addOrderItems(int $orderId, array $items) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (
                order_id, product_id, variation_id, quantity, unit_price,
                product_name, variation_name
            ) VALUES (
                :order_id, :product_id, :variation_id, :quantity, :unit_price,
                :product_name, :variation_name
            )
        ");

        foreach ($items as $item) {
            $stmt->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'product_name' => $item['product_name'],
                'variation_name' => $item['variation_name']
            ]);
        }
    }

    // ================ Métodos de Consulta e Atualização de Pedidos ================
    /**
     * Retorna todos os pedidos
     * @return array Lista de pedidos
     */
    public function getAllOrders() {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY id DESC");
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $orders;
    }

    /**
     * Retorna um pedido específico por ID
     * @param int $id ID do pedido
     * @return array Detalhes do pedido
     * @throws Exception Se o pedido não for encontrado
     */
    public function getOrderById(int $id) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders WHERE id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        $order['items'] = $this->getOrderItems($id);

        return $order;
    }

    /**
     * Retorna os itens de um pedido específico
     * @param int $orderId ID do pedido
     * @return array Lista de itens do pedido
     */
    public function getOrderItems(int $orderId) {
        $stmt = $this->db->prepare("
            SELECT * FROM order_items WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza o status de um pedido
     * @param int $id ID do pedido
     * @param string $status Novo status do pedido
     * @return bool Retorna true se a atualização for bem-sucedida
     * @throws Exception Se o status for inválido ou o pedido não for encontrado
     */
    public function updateOrderStatus(int $id, string $status) {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid order status', 400);
        }

        $stmt = $this->db->prepare("
            UPDATE orders SET status = ? WHERE id = ?
        ");
        $stmt->execute([$status, $id]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Order not found', 404);
        }

        // Se foi cancelado, devolver itens ao estoque através do ProductService
        if ($status === 'cancelled') {
            $this->restoreStock($id);
        }

        return true;
    }

    /**
     * Deleta um pedido e seus itens
     * @param int $orderId ID do pedido a ser deletado
     * @throws Exception Se ocorrer um erro ao deletar o pedido
     */
    public function deleteOrder(int $orderId) {
        $this->db->beginTransaction();

        try {
            // Remove itens do pedido
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);

            // Remove pedido
            $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);

            $this->db->commit();

        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw new Exception('Failed to delete order', 500);
        }
    }

    /**
     * Restaura o estoque dos itens de um pedido cancelado
     * @param int $orderId ID do pedido
     */
    private function restoreStock(int $orderId) {
        $items = $this->getOrderItems($orderId);

        foreach ($items as $item) {
            $this->productService->increaseStock(
                $item['variation_id'],
                $item['quantity']
            );
        }
    }

    // ================ Métodos Auxiliares ================

    /**
     * Obtém informações do CEP usando a API ViaCEP
     * @param string $cep CEP a ser consultado
     * @return array Informações do CEP
     */
    private function getCepInfo($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Atualiza um pedido com os dados fornecidos
     * @param int $id ID do pedido a ser atualizado
     * @param OrderUpdateDTO $dto Dados de atualização do pedido
     * @return bool Retorna true se a atualização for bem-sucedida
     * @throws Exception Se não houver campos para atualizar ou o pedido não for encontrado
     */
    public function updateOrder($id, OrderUpdateDTO $dto) {
        $fields = [];
        $params = [];
        if ($dto->status !== null) {
            $fields[] = "status = ?";
            $params[] = $dto->status;
        }
        if ($dto->customer_name !== null) {
            $fields[] = "customer_name = ?";
            $params[] = $dto->customer_name;
        }
        if ($dto->customer_email !== null) {
            $fields[] = "customer_email = ?";
            $params[] = $dto->customer_email;
        }
        if ($dto->customer_address !== null) {
            $fields[] = "customer_address = ?";
            $params[] = $dto->customer_address;
        }
        if ($dto->customer_cep !== null) {
            $fields[] = "customer_cep = ?";
            $params[] = $dto->customer_cep;
        }
        if ($dto->customer_neighborhood !== null) {
            $fields[] = "customer_neighborhood = ?";
            $params[] = $dto->customer_neighborhood;
        }
        if ($dto->customer_city !== null) {
            $fields[] = "customer_city = ?";
            $params[] = $dto->customer_city;
        }
        if ($dto->customer_state !== null) {
            $fields[] = "customer_state = ?";
            $params[] = $dto->customer_state;
        }
        if ($dto->customer_complement !== null) {
            $fields[] = "customer_complement = ?";
            $params[] = $dto->customer_complement;
        }
        if ($dto->coupon_code !== null) {
            $fields[] = "coupon_code = ?";
            $params[] = $dto->coupon_code;
        }
        if (!$fields) throw new \Exception("No fields to update", 400);
        $params[] = $id;
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return true;
    }
}
