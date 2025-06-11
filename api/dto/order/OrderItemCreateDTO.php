<?php
namespace dto\order;

class OrderItemCreateDTO {
    public int $product_id;
    public ?int $variation_id;
    public int $quantity;
    public float $unit_price;
    public string $product_name;
    public ?string $variation_name;

    public function __construct(array $data) {
        $this->product_id = (int)($data['product_id'] ?? 0);
        $this->variation_id = isset($data['variation_id']) ? (int)$data['variation_id'] : null;
        $this->quantity = (int)($data['quantity'] ?? 0);
        $this->unit_price = isset($data['unit_price']) ? (float)$data['unit_price'] : 0.0;
        $this->product_name = $data['product_name'] ?? '';
        $this->variation_name = $data['variation_name'] ?? null;
    }

    public function isValid(): bool {
        return $this->product_id > 0 && $this->quantity > 0 && $this->unit_price > 0;
    }
}