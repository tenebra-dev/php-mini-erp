<?php
use PHPUnit\Framework\TestCase;
use services\CouponService;
use migrations\MigrationRunner;

require_once __DIR__ . '/../../api/services/CouponService.php';
require_once __DIR__ . '/../../api/migrations/MigrationRunner.php';

class CouponServiceTest extends TestCase
{
    private $couponService;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $runner = new MigrationRunner($pdo);
        $runner->run();
        $this->couponService = new CouponService($pdo);

        // Insere um cupom válido
        $this->couponService->createCoupon([
            'code' => 'SUMMER21',
            'discount_value' => 20,
            'discount_type' => 'fixed',
            'min_value' => 0,
            'valid_until' => date('Y-m-d', strtotime('+1 day'))
        ]);
        // Insere um cupom expirado
        $this->couponService->createCoupon([
            'code' => 'EXPIRED',
            'discount_value' => 20,
            'discount_type' => 'fixed',
            'min_value' => 0,
            'valid_until' => date('Y-m-d', strtotime('-1 day'))
        ]);
    }

    public function testApplyCoupon()
    {
        $coupon = $this->couponService->validateCoupon('SUMMER21', 100);
        $this->assertEquals('SUMMER21', $coupon['code']);
    }

    public function testInvalidCoupon()
    {
        $this->expectException(Exception::class);
        $this->couponService->validateCoupon('INVALID', 100);
    }

    public function testExpiredCoupon()
    {
        $this->expectException(Exception::class);
        $this->couponService->validateCoupon('EXPIRED', 100);
    }
}