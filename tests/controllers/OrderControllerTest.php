<?php

use PHPUnit\Framework\TestCase;
use controllers\OrderController;
use migrations\MigrationRunner;

require_once __DIR__ . '/../../api/controllers/OrderController.php';
require_once __DIR__ . '/../../api/services/OrderService.php';
require_once __DIR__ . '/../../api/migrations/MigrationRunner.php';

class OrderControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        // Não chame session_start() aqui!
        $_SESSION = []; // Limpa a sessão para cada teste

        $pdo = new PDO('sqlite::memory:');
        $runner = new MigrationRunner($pdo);
        $runner->run();

        $this->controller = new OrderController($pdo);
    }

    /**
     * @runInSeparateProcess
     */
    public function testListOrders()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->controller->handleOrders([], []);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('data', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetCart()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->controller->handleCart([], []);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('items', $result['data']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddToCart()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $data = [
            'product_id' => 1,
            'quantity' => 2
        ];
        $result = $this->controller->handleCart([], $data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testClearCart()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $result = $this->controller->handleCart([], []);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHandleOrdersMethodNotAllowed()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST'; // Só aceita GET
        $result = $this->controller->handleOrders([], []);
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals(405, $result['code']);
        $this->assertStringContainsString('Method not allowed', $result['message']);
    }
}