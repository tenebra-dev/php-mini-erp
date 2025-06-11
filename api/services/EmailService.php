<?php
namespace services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use interfaces\EmailServiceInterface;
use dto\email\SendEmailDTO;
use dto\email\OrderStatusEmailDTO;
use dto\email\OrderCancellationEmailDTO;

/**
 * Classe EmailService
 * 
 * Esta classe é responsável por enviar e-mails de confirmação de pedidos, atualizações de status e cancelamentos.
 * Utiliza a biblioteca PHPMailer para envio de e-mails via SMTP.
 */
class EmailService implements EmailServiceInterface
{
    /**
     * Instância do PHPMailer.
     *
     * @var PHPMailer
     */
    private $mailer;

    /**
     * Construtor da classe EmailService.
     * Configura o PHPMailer com as credenciais SMTP e remetente.
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        // Configuração SMTP
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
     *
     * @param int $orderId ID do pedido.
     * @param array $orderData Dados do pedido, incluindo informações do cliente e itens.
     * @return bool Retorna true se o e-mail foi enviado com sucesso, caso contrário false.
     */
    public function sendOrderConfirmation(SendEmailDTO $dto): bool
    {
        if (!$dto->isValid()) return false;
        return $this->sendMail($dto);
    }

    /**
     * Envia um e-mail genérico.
     *
     * @param string $to Endereço de e-mail do destinatário.
     * @param string $name Nome do destinatário.
     * @param string $subject Assunto do e-mail.
     * @param string $body Corpo do e-mail em HTML.
     * @param string $altBody Corpo alternativo do e-mail em texto simples.
     * @return bool Retorna true se o e-mail foi enviado com sucesso, caso contrário false.
     */
    public function sendMail(SendEmailDTO $dto): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($dto->to, $dto->name);
            $this->mailer->Subject = $dto->subject;
            $this->mailer->Body = $dto->body;
            $this->mailer->AltBody = $dto->altBody;
            $this->mailer->isHTML(true);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Envia um e-mail de atualização de status do pedido.
     *
     * @param int $orderId ID do pedido.
     * @param string $customerEmail E-mail do cliente.
     * @param string $status Novo status do pedido.
     * @return bool Retorna true se o e-mail foi enviado com sucesso, caso contrário false.
     */
    public function sendOrderStatusUpdate(OrderStatusEmailDTO $dto): bool
    {
        if (!$dto->isValid()) return false;
        $subject = "Atualização do Pedido #{$dto->orderId}";
        $body = "Olá,<br>O status do seu pedido #{$dto->orderId} foi atualizado para: <b>{$dto->status}</b>.";
        $altBody = "Olá,\nO status do seu pedido #{$dto->orderId} foi atualizado para: {$dto->status}.";
        $sendDto = new SendEmailDTO([
            'to' => $dto->customerEmail,
            'name' => '',
            'subject' => $subject,
            'body' => $body,
            'altBody' => $altBody
        ]);
        return $this->sendMail($sendDto);
    }

    /**
     * Envia um e-mail de cancelamento do pedido.
     *
     * @param int $orderId ID do pedido.
     * @param string $customerEmail E-mail do cliente.
     * @return bool Retorna true se o e-mail foi enviado com sucesso, caso contrário false.
     */
    public function sendOrderCancellation(OrderCancellationEmailDTO $dto): bool
    {
        if (!$dto->isValid()) return false;
        $subject = "Pedido #{$dto->orderId} Cancelado";
        $body = "Olá,<br>Seu pedido #{$dto->orderId} foi cancelado.<br>O estoque dos produtos foi restaurado.";
        $altBody = "Olá,\nSeu pedido #{$dto->orderId} foi cancelado.\nO estoque dos produtos foi restaurado.";
        $sendDto = new SendEmailDTO([
            'to' => $dto->customerEmail,
            'name' => '',
            'subject' => $subject,
            'body' => $body,
            'altBody' => $altBody
        ]);
        return $this->sendMail($sendDto);
    }

    /**
     * Constrói a mensagem de confirmação do pedido.
     *
     * @param int $orderId ID do pedido.
     * @param array $orderData Dados do pedido, incluindo informações do cliente e itens.
     * @return string Mensagem formatada para o e-mail.
     */
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
}
