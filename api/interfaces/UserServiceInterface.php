<?php
namespace interfaces;

use dto\UserCreateDTO;
use dto\UserUpdateDTO;

interface UserServiceInterface {
    public function getAllUsers();
    public function getUserById($id);
    public function createUser(UserCreateDTO $dto);
    public function updateUser($id, UserUpdateDTO $dto);
    public function deleteUser($id);
}