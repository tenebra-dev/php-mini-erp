<?php
namespace models;

class Product {
    public int $id;
    public string $name;
    public ?string $sku;
    public float $price;
    public ?string $description;
    public ?string $image;
    public ?int $quantity;
    public ?array $variations;
    public ?array $stock;
    public string $created_at;
    public string $updated_at;

    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->sku = $data['sku'] ?? null;
        $this->price = isset($data['price']) ? (float)$data['price'] : 0;
        $this->description = $data['description'] ?? null;
        $this->image = $data['image'] ?? null;
        $this->quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
        $this->variations = $data['variations'] ?? null;
        $this->stock = $data['stock'] ?? null;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'description' => $this->description,
            'image' => $this->image,
            'variations' => $this->variations,
            'stock' => $this->stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}