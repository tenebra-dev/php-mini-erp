<?php
namespace services;

use \PDO;
use \Exception;
use interfaces\UserServiceInterface;
use dto\user\UserCreateDTO;
use dto\user\UserUpdateDTO;

/**
 * UserService class for managing user operations.
 */
class UserService implements UserServiceInterface {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser(UserCreateDTO $dto) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $dto->name,
            $dto->email,
            password_hash($dto->password, PASSWORD_DEFAULT),
            $dto->role
        ]);
        return $this->db->lastInsertId();
    }

    public function updateUser($id, UserUpdateDTO $dto) {
        $fields = [];
        $params = [];
        if ($dto->name !== null) {
            $fields[] = "name = ?";
            $params[] = $dto->name;
        }
        if ($dto->email !== null) {
            $fields[] = "email = ?";
            $params[] = $dto->email;
        }
        if ($dto->password !== null) {
            $fields[] = "password = ?";
            $params[] = password_hash($dto->password, PASSWORD_DEFAULT);
        }
        if ($dto->role !== null) {
            $fields[] = "role = ?";
            $params[] = $dto->role;
        }
        if (!$fields) throw new \Exception("No fields to update", 400);
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return true;
    }

    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
}