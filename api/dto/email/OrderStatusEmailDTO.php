<?php
namespace dto\email;

class OrderStatusEmailDTO {
    public int $orderId;
    public string $customerEmail;
    public string $status;

    public function __construct(array $data) {
        $this->orderId = (int)($data['orderId'] ?? 0);
        $this->customerEmail = $data['customerEmail'] ?? '';
        $this->status = $data['status'] ?? '';
    }

    public function isValid(): bool {
        return $this->orderId > 0 && !empty($this->customerEmail) && !empty($this->status);
    }
}