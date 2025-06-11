<?php
namespace interfaces;

use dto\coupon\CouponCreateDTO;
use dto\coupon\CouponUpdateDTO;

interface CouponServiceInterface {
    public function getAllCoupons();
    public function getCouponByCode(string $code);
    public function createCoupon(CouponCreateDTO $dto);
    public function updateCoupon(string $code, CouponUpdateDTO $dto);
    public function deleteCoupon(string $code);
    public function validateCoupon(string $code, float $subtotal);
}