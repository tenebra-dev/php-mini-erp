<?php
namespace controllers;

use services\UserService;
use \Exception;
use dto\UserCreateDTO;
use dto\UserUpdateDTO;

class UserController {
    private $userService;

    public function __construct(\PDO $db) {
        $this->userService = new UserService($db);
    }

    /**
     * Cria um novo usuário
     * @param array $params
     * @param array $data
     * @return array
     */
    public function handleUsers($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            switch ($method) {
                case 'GET':
                    $users = $this->userService->getAllUsers();
                    return ['success' => true, 'data' => $users];
                case 'POST':
                    $dto = new UserCreateDTO($data);
                    if (!$dto->isValid()) {
                        throw new Exception('Dados inválidos para criação de usuário', 400);
                    }
                    $userId = $this->userService->createUser($dto);
                    return ['success' => true, 'user_id' => $userId];
                default:
                    throw new Exception('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * Manipula operações específicas de um usuário
     * @param array $params Parâmetros da rota (ex: ['id' => 1])
     * @param array $data Dados enviados no corpo da requisição
     * @return array
     */
    public function handleUser($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $id = $params['id'] ?? null;
            if (!$id) throw new Exception('User ID is required', 400);
            switch ($method) {
                case 'GET':
                    $user = $this->userService->getUserById($id);
                    return ['success' => true, 'data' => $user];
                case 'PUT':
                    $dto = new UserUpdateDTO($data);
                    if (!$dto->isValid()) {
                        throw new Exception('Dados inválidos para atualização de usuário', 400);
                    }
                    $this->userService->updateUser($id, $dto);
                    return ['success' => true, 'message' => 'User updated'];
                case 'DELETE':
                    $this->userService->deleteUser($id);
                    return ['success' => true, 'message' => 'User deleted'];
                default:
                    throw new Exception('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
}