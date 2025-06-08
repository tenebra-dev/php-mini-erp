<?php
namespace services;

use \PDO;
use \Exception;

class CouponService {
    private $db;
    
    public function __construct(\PDO $db) {
        $this->db = $db;
    }
    
    public function getCouponByCode(string $code) {
        $stmt = $this->db->prepare("
            SELECT *, valid_until > NOW() AS is_valid 
            FROM coupons 
            WHERE code = ?
        ");
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function validateCoupon(string $code, float $subtotal) {
        $coupon = $this->getCouponByCode($code);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }
        
        if (!$coupon['is_valid']) {
            throw new Exception('Coupon is expired', 400);
        }
        
        if ($subtotal < $coupon['min_value']) {
            throw new Exception('Subtotal is less than minimum required', 400);
        }
        
        return $coupon;
    }

    public function getAllCoupons() {
        $stmt = $this->db->query("
            SELECT *, valid_until > NOW() AS is_valid 
            FROM coupons
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createCoupon(array $couponData) {
        $stmt = $this->db->prepare("
            INSERT INTO coupons (
                code, discount_value, discount_type, min_value, valid_until
            ) VALUES (
                :code, :discount_value, :discount_type, :min_value, :valid_until
            )
        ");
        
        $stmt->execute([
            'code' => $couponData['code'],
            'discount_value' => $couponData['discount_value'],
            'discount_type' => $couponData['discount_type'],
            'min_value' => $couponData['min_value'],
            'valid_until' => $couponData['valid_until']
        ]);
        
        return $this->db->lastInsertId();
    }

public function deleteCoupon(string $code) {
    $stmt = $this->db->prepare("DELETE FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    
    if ($stmt->rowCount() === 0) {
        throw new \Exception('Coupon not found', 404);
    }
    
    return true;
}
}
