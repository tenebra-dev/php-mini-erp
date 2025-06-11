<?php
namespace dto;

class CouponCreateDTO {
    public string $code;
    public float $discount_value;
    public string $discount_type;
    public float $min_value;
    public ?string $valid_until;

    public function __construct(array $data) {
        $this->code = $data['code'] ?? '';
        $this->discount_value = isset($data['discount_value']) ? (float)$data['discount_value'] : 0;
        $this->discount_type = $data['discount_type'] ?? 'fixed';
        $this->min_value = isset($data['min_value']) ? (float)$data['min_value'] : 0;
        $this->valid_until = $data['valid_until'] ?? null;
    }

    public function isValid(): bool {
        return !empty($this->code) && $this->discount_value > 0;
    }
}