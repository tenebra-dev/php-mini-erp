<?php
namespace dto\product;

/**
 * Data Transfer Object for updating a product.
 * Allows partial updates, meaning not all fields are required.
 */
class ProductUpdateDTO {
    public ?string $name;
    public ?float $price;
    public ?string $description;
    public ?int $quantity;
    public ?array $variations;

    public function __construct(array $data) {
        $this->name = $data['name'] ?? null;
        $this->price = isset($data['price']) ? (float)$data['price'] : null;
        $this->description = $data['description'] ?? null;
        $this->quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
        $this->variations = $data['variations'] ?? null;
    }

    public function isValid(): bool {
        // Permite atualização parcial, mas pelo menos um campo deve ser enviado
        return $this->name !== null || $this->price !== null || $this->description !== null || $this->quantity !== null || $this->variations !== null;
    }
}