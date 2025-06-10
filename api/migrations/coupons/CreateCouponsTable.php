<?php
namespace migrations\coupons;

use migrations\Migration;

class CreateCouponsTable extends Migration {
    public function up() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->execute("
                CREATE TABLE IF NOT EXISTS coupons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code TEXT NOT NULL UNIQUE,
                    discount_value REAL NOT NULL,
                    discount_type TEXT DEFAULT 'fixed',
                    min_value REAL DEFAULT 0,
                    valid_until TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_code ON coupons (code)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_valid_until ON coupons (valid_until)");
        } else {
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
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS coupons");
    }
}
