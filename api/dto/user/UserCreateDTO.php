<?php
namespace dto;

class UserCreateDTO {
    public string $name;
    public string $email;
    public string $password;
    public string $role;

    public function __construct(array $data) {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'user';
    }

    public function isValid(): bool {
        return !empty($this->name) && filter_var($this->email, FILTER_VALIDATE_EMAIL) && !empty($this->password);
    }
}