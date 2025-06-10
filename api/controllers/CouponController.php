<?php
namespace controllers;

use services\CouponService;
use \Exception;

class CouponController {
    private $couponService;
    
    public function __construct(\PDO $db) {
        $this->couponService = new CouponService($db);
    }
    
    public function handleCoupons($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            switch ($method) {
                case 'GET':
                    return $this->listCoupons();
                case 'POST':
                    return $this->createCoupon($data);
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[CouponController][handleCoupons] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    public function handleCoupon($params, $data) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $code = $params['code'] ?? null;
            if (!$code) {
                throw new \Exception('Coupon code is required', 400);
            }
            switch ($method) {
                case 'GET':
                    return $this->getCoupon($code);
                case 'DELETE':
                    return $this->deleteCoupon($code);
                default:
                    throw new \Exception('Method not allowed', 405);
            }
        } catch (\Exception $e) {
            error_log("[CouponController][handleCoupon] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
        }
    }
    
    private function listCoupons() {
        $coupons = $this->couponService->getAllCoupons();
        
        return [
            'success' => true,
            'data' => $coupons
        ];
    }
    
    private function createCoupon($data) {
        // Validação dos campos obrigatórios
        $requiredFields = ['code', 'discount_value'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Field $field is required", 400);
            }
        }
        
        // Preparar dados do cupom
        $couponData = [
            'code' => $data['code'],
            'discount_value' => $data['discount_value'],
            'discount_type' => $data['discount_type'] ?? 'fixed',
            'min_value' => $data['min_value'] ?? 0,
            'valid_until' => $data['valid_until'] ?? null
        ];
        
        // Criar o cupom
        $couponId = $this->couponService->createCoupon($couponData);
        
        return [
            'success' => true,
            'message' => 'Coupon created successfully',
            'coupon_id' => $couponId
        ];
    }
    
    private function getCoupon($code) {
        $coupon = $this->couponService->getCouponByCode($code);
        
        if (!$coupon) {
            throw new \Exception('Coupon not found', 404);
        }
        
        return [
            'success' => true,
            'data' => $coupon
        ];
    }
    
    private function deleteCoupon($code) {
        $this->couponService->deleteCoupon($code);
        
        return [
            'success' => true,
            'message' => 'Coupon deleted successfully'
        ];
    }
}
