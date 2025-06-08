<?php
namespace migrations\coupons;

use migrations\Migration;

class CreateCouponsTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS coupons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(20) NOT NULL UNIQUE,
                discount_value DECIMAL(10,2) NOT NULL,
                discount_type ENUM('percentage', 'fixed') DEFAULT 'fixed',
                min_value DECIMAL(10,2) DEFAULT 0,
                valid_until DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_code (code),
                INDEX idx_valid_until (valid_until)
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS coupons");
    }
}
