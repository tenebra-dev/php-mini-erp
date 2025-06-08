<?php
namespace migrations\orders;

use migrations\Migration;

class CreateOrdersTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(100) NOT NULL,
                customer_email VARCHAR(100) NOT NULL,
                customer_cep VARCHAR(10) NOT NULL,
                customer_address TEXT NOT NULL,
                customer_neighborhood VARCHAR(100),
                customer_city VARCHAR(50),
                customer_state VARCHAR(2),
                subtotal DECIMAL(10,2) NOT NULL,
                shipping DECIMAL(10,2) NOT NULL,
                discount DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) NOT NULL,
                coupon_code VARCHAR(20),
                status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status),
                INDEX idx_customer_email (customer_email),
                INDEX idx_created_at (created_at),
                INDEX idx_coupon_code (coupon_code)
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS orders");
    }
}
