<?php
namespace dto;

class OrderUpdateDTO {
    public ?string $status;
    public ?string $customer_name;
    public ?string $customer_email;
    public ?string $customer_address;
    public ?string $customer_cep;
    public ?string $customer_neighborhood;
    public ?string $customer_city;
    public ?string $customer_state;
    public ?string $customer_complement;
    public ?string $coupon_code;

    public function __construct(array $data) {
        $this->status = $data['status'] ?? null;
        $this->customer_name = $data['customer_name'] ?? null;
        $this->customer_email = $data['customer_email'] ?? null;
        $this->customer_address = $data['customer_address'] ?? null;
        $this->customer_cep = $data['customer_cep'] ?? null;
        $this->customer_neighborhood = $data['customer_neighborhood'] ?? null;
        $this->customer_city = $data['customer_city'] ?? null;
        $this->customer_state = $data['customer_state'] ?? null;
        $this->customer_complement = $data['customer_complement'] ?? null;
        $this->coupon_code = $data['coupon_code'] ?? null;
    }

    public function isValid(): bool {
        // Permite atualização parcial, mas pelo menos um campo deve ser enviado
        return $this->status !== null ||
               $this->customer_name !== null ||
               $this->customer_email !== null ||
               $this->customer_address !== null ||
               $this->customer_cep !== null ||
               $this->customer_neighborhood !== null ||
               $this->customer_city !== null ||
               $this->customer_state !== null ||
               $this->customer_complement !== null ||
               $this->coupon_code !== null;
    }
}