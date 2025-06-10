<?php

use PHPUnit\Framework\TestCase;
use controllers\CouponController;
use migrations\MigrationRunner;

require_once __DIR__ . '/../../api/controllers/CouponController.php';
require_once __DIR__ . '/../../api/services/CouponService.php';
require_once __DIR__ . '/../../api/migrations/MigrationRunner.php';

class CouponControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        // Rodar as migrations para criar as tabelas
        $runner = new MigrationRunner($pdo);
        $runner->run();

        $this->controller = new CouponController($pdo);
    }

    public function testListCoupons()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->controller->handleCoupons([], []);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function testCreateCoupon()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $data = [
            'code' => 'SUMMER21',
            'discount_value' => 20
        ];
        $result = $this->controller->handleCoupons([], $data);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('coupon_id', $result);
    }

    public function testCreateCouponValidationError()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $data = [
            // 'code' => 'FALTA', // omitido para forçar erro
            'discount_value' => 10
        ];
        $result = $this->controller->handleCoupons([], $data);
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(400, $result['code']);
    }

    public function testHandleCouponsMethodNotAllowed()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT'; // Só aceita GET e POST
        $result = $this->controller->handleCoupons([], []);
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals(405, $result['code']);
        $this->assertStringContainsString('Method not allowed', $result['message']);
    }
}