<?php
namespace services;

class EmailService {
    
    public function sendOrderConfirmation($orderId, $orderData) {
        // Simulação do envio de email
        $subject = "Confirmação do Pedido #{$orderId}";
        $message = $this->buildOrderConfirmationMessage($orderId, $orderData);
        
        // Em produção, você usaria uma biblioteca de email como PHPMailer
        error_log("Email enviado para {$orderData['customer_email']}:\n{$message}");
        
        return true;
    }
    
    private function buildOrderConfirmationMessage($orderId, $orderData) {
        $message = "Olá {$orderData['customer_name']},\n\n";
        $message .= "Seu pedido #{$orderId} foi recebido com sucesso!\n\n";
        $message .= "Itens:\n";
        
        foreach ($orderData['items'] as $item) {
            $message .= "- {$item['product_name']} ({$item['variation_name']}): ";
            $message .= "{$item['quantity']} x R$ " . number_format($item['unit_price'], 2) . "\n";
        }
        
        $message .= "\n";
        $message .= "Subtotal: R$ " . number_format($orderData['subtotal'], 2) . "\n";
        $message .= "Frete: R$ " . number_format($orderData['shipping'], 2) . "\n";
        $message .= "Desconto: R$ " . number_format($orderData['discount'], 2) . "\n";
        $message .= "Total: R$ " . number_format($orderData['total'], 2) . "\n\n";
        $message .= "Endereço de entrega:\n";
        $message .= "{$orderData['customer_address']}\n";
        $message .= "{$orderData['customer_neighborhood']} - {$orderData['customer_city']}/{$orderData['customer_state']}\n";
        $message .= "CEP: {$orderData['customer_cep']}\n";
        
        return $message;
    }
    
    public function sendOrderStatusUpdate($orderId, $customerEmail, $status) {
        $subject = "Atualização do Pedido #{$orderId}";
        $message = "Olá,\n\n";
        $message .= "O status do seu pedido #{$orderId} foi atualizado para: {$status}\n";
        
        error_log("Email de status enviado para {$customerEmail}:\n{$message}");
        
        return true;
    }
    
    public function sendOrderCancellation($orderId, $customerEmail) {
        $subject = "Pedido #{$orderId} Cancelado";
        $message = "Olá,\n\n";
        $message .= "Seu pedido #{$orderId} foi cancelado.\n";
        $message .= "O estoque dos produtos foi restaurado.\n";
        
        error_log("Email de cancelamento enviado para {$customerEmail}:\n{$message}");
        
        return true;
    }
}
