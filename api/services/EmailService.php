<?php
namespace services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        // Configuração SMTP (ideal: use variáveis de ambiente)
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('SMTP_HOST') ?: 'smtp.seuprovedor.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('SMTP_USER') ?: 'usuario@dominio.com';
        $this->mailer->Password = getenv('SMTP_PASS') ?: 'senha';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = getenv('SMTP_PORT') ?: 587;
        $this->mailer->setFrom(getenv('MAIL_FROM') ?: 'no-reply@dominio.com', 'Mini ERP');
    }

    /**
     * Envia a confirmação do pedido para o cliente.
     * Em produção, use uma biblioteca como PHPMailer.
     */
    public function sendOrderConfirmation($orderId, $orderData)
    {
        $subject = "Confirmação do Pedido #{$orderId}";
        $body = nl2br($this->buildOrderConfirmationMessage($orderId, $orderData));
        $altBody = $this->buildOrderConfirmationMessage($orderId, $orderData);

        return $this->sendMail($orderData['customer_email'], $orderData['customer_name'], $subject, $body, $altBody);
    }

    private function sendMail($to, $name, $subject, $body, $altBody)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $name);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody;
            $this->mailer->isHTML(true);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: {$e->getMessage()}");
            return false;
        }
    }

    private function buildOrderConfirmationMessage($orderId, $orderData)
    {
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

    public function sendOrderStatusUpdate($orderId, $customerEmail, $status)
    {
        $subject = "Atualização do Pedido #{$orderId}";
        $message = "Olá,\n\n";
        $message .= "O status do seu pedido #{$orderId} foi atualizado para: {$status}\n";
        error_log("Email de status enviado para {$customerEmail}:\n{$message}");
        return true;
    }

    public function sendOrderCancellation($orderId, $customerEmail)
    {
        $subject = "Pedido #{$orderId} Cancelado";
        $message = "Olá,\n\n";
        $message .= "Seu pedido #{$orderId} foi cancelado.\n";
        $message .= "O estoque dos produtos foi restaurado.\n";
        error_log("Email de cancelamento enviado para {$customerEmail}:\n{$message}");
        return true;
    }
}
