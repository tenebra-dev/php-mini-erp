<?php
namespace models;

class User {
    public int $id;
    public string $name;
    public string $email;
    public string $role;
    public string $created_at;
    public string $updated_at;

    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->role = $data['role'] ?? 'user';
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}