<?php
namespace dto\email;

class OrderCancellationEmailDTO {
    public int $orderId;
    public string $customerEmail;

    public function __construct(array $data) {
        $this->orderId = (int)($data['orderId'] ?? 0);
        $this->customerEmail = $data['customerEmail'] ?? '';
    }

    public function isValid(): bool {
        return $this->orderId > 0 && !empty($this->customerEmail);
    }
}