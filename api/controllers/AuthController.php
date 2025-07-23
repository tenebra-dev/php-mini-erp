<?php
namespace controllers;

use services\UserService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $userService;
    private $jwtSecret = 'SUA_CHAVE_SECRETA'; // Troque por uma chave forte

    public function __construct(\PDO $db) {
        $this->userService = new UserService($db);
    }

    public function login($params, $data) {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $user = $this->userService->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Credenciais inválidas', 'code' => 401];
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + 3600 // 1 hora
        ];
        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        return ['success' => true, 'token' => $jwt, 'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]];
    }

    public function verify($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array)$decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
}