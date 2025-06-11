<?php
namespace services;

use \PDO;
use \Exception;
use interfaces\CouponServiceInterface;
use dto\coupon\CouponCreateDTO;
use dto\coupon\CouponUpdateDTO;

/**
 * CouponService class for managing coupon operations.
 */
class CouponService implements CouponServiceInterface {
    /**
     * @var \PDO Database connection instance
     */
    private $db;
    
    /**
     * Constructor to initialize the database connection.
     *
     * @param \PDO $db The PDO instance for database operations.
     */
    public function __construct(\PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Retrieves a coupon by its code.
     *
     * @param string $code The coupon code.
     * @return array|null The coupon data or null if not found.
     * @throws Exception If the coupon is expired or invalid.
     */
    public function getCouponByCode(string $code) {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $this->db->prepare("
                SELECT *, 
                    (valid_until IS NULL OR datetime(valid_until) > datetime('now')) AS is_valid 
                FROM coupons 
                WHERE code = ?
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT *, 
                    valid_until > NOW() AS is_valid 
                FROM coupons 
                WHERE code = ?
            ");
        }
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Validates a coupon against the subtotal.
     *
     * @param string $code The coupon code.
     * @param float $subtotal The subtotal amount.
     * @return array The validated coupon data.
     * @throws Exception If the coupon is not found, expired, or the subtotal is less than the minimum required.
     */
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

    /**
     * Retrieves all coupons, including their validity status.
     *
     * @return array List of all coupons with validity status.
     */
    public function getAllCoupons() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $this->db->query("
                SELECT *, 
                    (valid_until IS NULL OR datetime(valid_until) > datetime('now')) AS is_valid 
                FROM coupons
            ");
        } else {
            $stmt = $this->db->query("
                SELECT *, 
                    valid_until > NOW() AS is_valid 
                FROM coupons
            ");
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new coupon.
     *
     * @param CouponCreateDTO $dto The coupon data transfer object.
     * @return string The created coupon code.
     * @throws Exception If the coupon is invalid or already exists.
     */
    public function createCoupon(CouponCreateDTO $dto) {
        if (!$dto->isValid()) {
            throw new Exception('Invalid coupon data', 400);
        }

        // Check if the coupon already exists
        $existingCoupon = $this->getCouponByCode($dto->code);
        if ($existingCoupon) {
            throw new Exception('Coupon already exists', 409);
        }

        $stmt = $this->db->prepare("
            INSERT INTO coupons (code, discount_value, discount_type, min_value, valid_until) 
            VALUES (:code, :discount_value, :discount_type, :min_value, :valid_until)
        ");
        
        $stmt->execute([
            'code' => $dto->code,
            'discount_value' => $dto->discount_value,
            'discount_type' => $dto->discount_type,
            'min_value' => $dto->min_value,
            'valid_until' => $dto->valid_until
        ]);
        
        return $dto->code;
    }

    /**
     * Updates an existing coupon.
     *
     * @param string $code The coupon code to update.
     * @param CouponUpdateDTO $dto The updated coupon data transfer object.
     * @return bool True on success, false on failure.
     * @throws Exception If the coupon is not found or the data is invalid.
     */
    public function updateCoupon(string $code, CouponUpdateDTO $dto) {
        if (!$dto->isValid()) {
            throw new Exception('Invalid coupon data', 400);
        }

        // Check if the coupon exists
        $existingCoupon = $this->getCouponByCode($code);
        if (!$existingCoupon) {
            throw new Exception('Coupon not found', 404);
        }

        $fields = [];
        $params = [];
        
        if ($dto->discount_value !== null) {
            $fields[] = "discount_value = :discount_value";
            $params['discount_value'] = $dto->discount_value;
        }
        if ($dto->discount_type !== null) {
            $fields[] = "discount_type = :discount_type";
            $params['discount_type'] = $dto->discount_type;
        }
        if ($dto->min_value !== null) {
            $fields[] = "min_value = :min_value";
            $params['min_value'] = $dto->min_value;
        }
        if ($dto->valid_until !== null) {
            $fields[] = "valid_until = :valid_until";
            $params['valid_until'] = $dto->valid_until;
        }

        if (empty($fields)) {
            throw new Exception('No fields to update', 400);
        }

        $params['code'] = $code;
        
        $sql = "UPDATE coupons SET " . implode(', ', $fields) . " WHERE code = :code";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Deletes a coupon by its code.
     *
     * @param string $code The coupon code to delete.
     * @return bool True on success, false on failure.
     * @throws Exception If the coupon is not found.
     */
    public function deleteCoupon(string $code) {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->rowCount() === 0) {
            throw new \Exception('Coupon not found', 404);
        }
        
        return true;
    }
}
