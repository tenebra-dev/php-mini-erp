<?php
namespace models;

class Product {
    public int $id;
    public string $name;
    public float $price;
    public ?string $description;
    public ?int $quantity;
    public ?array $variations;
    public string $created_at;
    public string $updated_at;

    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->price = isset($data['price']) ? (float)$data['price'] : 0;
        $this->description = $data['description'] ?? null;
        $this->quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
        $this->variations = $data['variations'] ?? null;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}