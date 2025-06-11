<?php
namespace controllers;

use services\OrderService;
use \Exception;
use dto\OrderUpdateDTO;
use dto\OrderCreateDTO;

class OrderController {
    private $orderService;
    
    public function __construct(\PDO $db) {
        $this->orderService = new OrderService($db);
        
        // Inicializa o carrinho na sessão se não existir
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],
                'coupon' => null,
                'shipping' => 0,
                'subtotal' => 0,
                'total' => 0
            ];
        }
    }

    /**
     * Manipula as operações de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function __call($name, $arguments) {
        error_log("[OrderController] Method $name not found");
        return [
            'success' => false,
            'message' => 'Method not found',
            'code' => 404
        ];
    }

    /**
     * Manipula as operações de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handle($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method === 'POST' && isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
            switch ($method) {
                case 'GET':
                    return $this->handleOrders($params, $data);
                case 'POST':
                    return $this->handleCart($params, $data);
                case 'PUT':
                    return $this->handleCheckout($params, $data);
                case 'DELETE':
                    return $this->handleCart($params, $data);
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[OrderController][handle] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula as operações de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrders($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'GET') {
                throw new \Exception('Method not allowed', 405);
            }
            $orders = $this->orderService->getAllOrders();
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrders] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula as operações do carrinho
     */
    public function handleCart($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            switch ($method) {
                case 'GET':
                    return $this->getCart();
                case 'POST':
                    // Verifica se é para aplicar cupom ou adicionar item
                    if (isset($data['coupon_code'])) {
                        return $this->orderService->applyCoupon($data['coupon_code']);
                    } else {
                        return $this->orderService->addToCart($data);
                    }
                case 'DELETE':
                    if (isset($params['remove_coupon'])) {
                        return $this->orderService->removeCoupon();
                    } else {
                        return $this->orderService->clearCart();
                    }
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[OrderController][handleCart] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Processa o checkout/finalização do pedido
     */
    public function handleCheckout($params, $data) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed', 405);
            }
            return $this->orderService->processCheckout($data);
        } catch (\Exception $e) {
            error_log("[OrderController][handleCheckout] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Manipula webhooks de atualização de status
     */
    public function handleWebhook($params, $data) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed', 405);
            }
            return $this->orderService->processWebhook($data);
        } catch (\Exception $e) {
            error_log("[OrderController][handleWebhook] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    /**
     * Manipula operações específicas de um pedido
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrder($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            switch ($method) {
                case 'GET':
                    $order = $this->orderService->getOrderById($id);
                    return [
                        'success' => true,
                        'data' => $order
                    ];
                case 'PUT':
                    $dto = new OrderUpdateDTO($data);
                    if (!$dto->isValid()) {
                        throw new \Exception('Dados inválidos para atualização de pedido', 400);
                    }
                    $this->orderService->updateOrder($id, $dto);
                    return ['success' => true, 'message' => 'Pedido atualizado'];
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrder] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula operações de atualização de status de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderStatusUpdate($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'PUT') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            $dto = new OrderUpdateDTO($data);
            if (!$dto->isValid()) {
                throw new \Exception('Dados inválidos para atualização de status do pedido', 400);
            }
            $this->orderService->updateOrderStatus($id, $dto);
            return ['success' => true, 'message' => 'Status do pedido atualizado'];
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderStatusUpdate] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula operações de limpeza do carrinho
     * @param array $params Parâmetros da rota (ex: ['remove_coupon' => true])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleCartClear($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'DELETE') {
                throw new \Exception('Method not allowed', 405);
            }
            if (isset($params['remove_coupon']) && $params['remove_coupon'] === 'true') {
                return $this->orderService->removeCoupon();
            } else {
                return $this->orderService->clearCart();
            }
        } catch (\Exception $e) {
            error_log("[OrderController][handleCartClear] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

/**
     * Manipula operações de finalização do pedido
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderFinalization($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'POST') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            return $this->orderService->finalizeOrder($id, $data);
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderFinalization] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    /**
     * Manipula operações de cancelamento de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderCancellation($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'DELETE') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            return $this->orderService->cancelOrder($id);
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderCancellation] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    /**
     * Manipula operações de reembolso de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderRefund($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'POST') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            return $this->orderService->refundOrder($id, $data);
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderRefund] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    /**
     * Manipula operações de atualização de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderUpdate($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'PUT') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            $dto = new OrderUpdateDTO($data);
            if (!$dto->isValid()) {
                throw new \Exception('Dados inválidos para atualização de pedido', 400);
            }
            $this->orderService->updateOrder($id, $dto);
            return ['success' => true, 'message' => 'Pedido atualizado'];
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderUpdate] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula operações de atualização de status de pedidos
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleOrderStatus($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method !== 'PUT') {
                throw new \Exception('Method not allowed', 405);
            }
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Order ID is required', 400);
            }
            $dto = new OrderUpdateDTO($data);
            if (!$dto->isValid()) {
                throw new \Exception('Dados inválidos para atualização de status do pedido', 400);
            }
            $this->orderService->updateOrderStatus($id, $dto);
            return ['success' => true, 'message' => 'Status do pedido atualizado'];
        } catch (\Exception $e) {
            error_log("[OrderController][handleOrderStatus] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    // ================ Métodos auxiliares ================
    
    /**
     * Obtém o carrinho atual da sessão
     * @return array
     */
    private function getCart() {
        return [
            'success' => true,
            'data' => $_SESSION['cart']
        ];
    }
    
    /**
     * Remove um item do carrinho
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function removeFromCart($params, $data) {
        try {
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new \Exception('Item ID is required', 400);
            }
            return $this->orderService->removeItemFromCart($id);
        } catch (\Exception $e) {
            error_log("[OrderController][removeFromCart] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
}
