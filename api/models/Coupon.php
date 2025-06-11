<?php
namespace models;

class Coupon {
    public int $id;
    public string $code;
    public float $discount_value;
    public string $discount_type;
    public float $min_value;
    public ?string $valid_until;
    public bool $is_valid;

    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->code = $data['code'] ?? '';
        $this->discount_value = isset($data['discount_value']) ? (float)$data['discount_value'] : 0;
        $this->discount_type = $data['discount_type'] ?? 'fixed';
        $this->min_value = isset($data['min_value']) ? (float)$data['min_value'] : 0;
        $this->valid_until = $data['valid_until'] ?? null;
        $this->is_valid = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;
    }
}