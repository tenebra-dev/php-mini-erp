<?php
namespace interfaces;

use dto\email\SendEmailDTO;
use dto\email\OrderStatusEmailDTO;
use dto\email\OrderCancellationEmailDTO;

interface EmailServiceInterface
{
    /**
     * Envia a confirmação do pedido para o cliente.
     *
     * @param SendEmailDTO $dto
     * @return bool
     */
    public function sendOrderConfirmation(SendEmailDTO $dto): bool;

    /**
     * Envia um e-mail de atualização de status do pedido.
     *
     * @param OrderStatusEmailDTO $dto
     * @return bool
     */
    public function sendOrderStatusUpdate(OrderStatusEmailDTO $dto): bool;

    /**
     * Envia um e-mail de cancelamento do pedido.
     *
     * @param OrderCancellationEmailDTO $dto
     * @return bool
     */
    public function sendOrderCancellation(OrderCancellationEmailDTO $dto): bool;
}