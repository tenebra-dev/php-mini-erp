<?php
namespace interfaces;

use dto\order\OrderCreateDTO;
use dto\order\OrderUpdateDTO;

interface OrderServiceInterface {
    // CRUD de pedidos
    public function getAllOrders();
    public function getOrderById(int $id);
    public function createOrder(OrderCreateDTO $dto);
    public function updateOrder(int $id, OrderUpdateDTO $dto);
    public function deleteOrder(int $orderId);

    // Itens do pedido
    public function getOrderItems(int $orderId);

    // Status do pedido
    public function updateOrderStatus(int $id, string $status);

    // Carrinho
    public function addToCart(array $data);
    public function getCart();
    public function removeItemFromCart(string $itemKey);
    public function clearCart();
    public function applyCoupon(string $couponCode);
    public function removeCoupon();

    // Checkout
    public function processCheckout(array $data);

    // Webhook
    public function processWebhook(array $data);

    // Métodos auxiliares
    // public function getCepInfo(string $cep);
}