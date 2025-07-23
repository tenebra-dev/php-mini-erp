<?php
namespace dto\order;

class OrderCreateDTO {
    public string $customer_name;
    public string $customer_email;
    public string $customer_cep;
    public string $customer_address;
    public ?string $customer_complement;
    public ?string $customer_neighborhood;
    public ?string $customer_city;
    public ?string $customer_state;
    public ?string $coupon_code;
    public float $subtotal;
    public float $shipping;
    public float $discount;
    public float $total;
    public string $status;
    /** @var OrderItemCreateDTO[] */
    public array $items;

    public function __construct(array $data) {
        $this->customer_name = $data['customer_name'] ?? '';
        $this->customer_email = $data['customer_email'] ?? '';
        $this->customer_cep = $data['customer_cep'] ?? '';
        $this->customer_address = $data['customer_address'] ?? '';
        $this->customer_complement = $data['customer_complement'] ?? null;
        $this->customer_neighborhood = $data['customer_neighborhood'] ?? null;
        $this->customer_city = $data['customer_city'] ?? null;
        $this->customer_state = $data['customer_state'] ?? null;
        $this->coupon_code = $data['coupon_code'] ?? null;
        $this->subtotal = isset($data['subtotal']) ? (float)$data['subtotal'] : 0;
        $this->shipping = isset($data['shipping']) ? (float)$data['shipping'] : 0;
        $this->discount = isset($data['discount']) ? (float)$data['discount'] : 0;
        $this->total = isset($data['total']) ? (float)$data['total'] : 0;
        $this->status = $data['status'] ?? 'pending';
        $this->items = [];
        foreach (($data['items'] ?? []) as $item) {
            $this->items[] = new OrderItemCreateDTO($item);
        }
    }

    public function isValid(): bool {
        return !empty($this->customer_name)
            && filter_var($this->customer_email, FILTER_VALIDATE_EMAIL)
            && !empty($this->customer_cep)
            && !empty($this->customer_address)
            && count($this->items) > 0
            && array_reduce($this->items, fn($carry, $item) => $carry && $item->isValid(), true);
    }
}