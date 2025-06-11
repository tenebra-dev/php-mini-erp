<?php
namespace dto;

class UserUpdateDTO {
    public ?string $name;
    public ?string $email;
    public ?string $password;
    public ?string $role;

    public function __construct(array $data) {
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? null;
    }

    public function isValid(): bool {
        // Permite atualização parcial, mas se algum campo vier, valida
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return $this->name !== null || $this->email !== null || $this->password !== null || $this->role !== null;
    }
}