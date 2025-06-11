<?php
namespace models;

class OrderItem {
    public int $id;
    public int $order_id;
    public int $product_id;
    public ?int $variation_id;
    public int $quantity;
    public float $unit_price;
    public string $product_name;
    public ?string $variation_name;

    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->order_id = (int)($data['order_id'] ?? 0);
        $this->product_id = (int)($data['product_id'] ?? 0);
        $this->variation_id = isset($data['variation_id']) ? (int)$data['variation_id'] : null;
        $this->quantity = (int)($data['quantity'] ?? 0);
        $this->unit_price = isset($data['unit_price']) ? (float)$data['unit_price'] : 0;
        $this->product_name = $data['product_name'] ?? '';
        $this->variation_name = $data['variation_name'] ?? null;
    }
}