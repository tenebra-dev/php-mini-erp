<?php
namespace dto;

class CouponUpdateDTO {
    public ?string $code;
    public ?float $discount_value;
    public ?string $discount_type;
    public ?float $min_value;
    public ?string $valid_until;

    public function __construct(array $data) {
        $this->code = $data['code'] ?? null;
        $this->discount_value = isset($data['discount_value']) ? (float)$data['discount_value'] : null;
        $this->discount_type = $data['discount_type'] ?? null;
        $this->min_value = isset($data['min_value']) ? (float)$data['min_value'] : null;
        $this->valid_until = $data['valid_until'] ?? null;
    }

    public function isValid(): bool {
        return $this->code !== null || $this->discount_value !== null || $this->discount_type !== null || $this->min_value !== null || $this->valid_until !== null;
    }
}