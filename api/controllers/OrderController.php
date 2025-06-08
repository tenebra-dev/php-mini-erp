<?php
namespace controllers;

use services\OrderService;
use \Exception;

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

    public function handleOrders($params, $data) {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method !== 'GET') {
            throw new \Exception('Method not allowed', 405);
        }
        // Exemplo: buscar todos os pedidos
        $orders = $this->orderService->getAllOrders();
        return [
            'success' => true,
            'data' => $orders
        ];
    }

    /**
     * Manipula as operações do carrinho
     */
    public function handleCart($params, $data) {
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
    }
    
    /**
     * Processa o checkout/finalização do pedido
     */
    public function handleCheckout($params, $data) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new \Exception('Method not allowed', 405);
        }
        
        return $this->orderService->processCheckout($data);
    }
    
    /**
     * Manipula webhooks de atualização de status
     */
    public function handleWebhook($params, $data) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new \Exception('Method not allowed', 405);
        }
        
        return $this->orderService->processWebhook($data);
    }
    
    // ================ Métodos auxiliares ================
    
    private function getCart() {
        return [
            'success' => true,
            'data' => $_SESSION['cart']
        ];
    }
}
