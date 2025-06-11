<?php
namespace dto\product;

/**
 * ProductCreateDTO is a Data Transfer Object for creating a product.
 * It encapsulates the data required to create a product and provides validation.
 */
class ProductCreateDTO {
    public string $name;
    public float $price;
    public ?string $description;
    public ?int $quantity;
    public ?array $variations;

    public function __construct(array $data) {
        $this->name = $data['name'] ?? '';
        $this->price = isset($data['price']) ? (float)$data['price'] : 0;
        $this->description = $data['description'] ?? null;
        $this->quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
        $this->variations = $data['variations'] ?? null;
    }

    public function isValid(): bool {
        return !empty($this->name) && $this->price > 0;
    }
}