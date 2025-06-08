<?php
namespace migrations\products;

use migrations\Migration;

class CreateProductsTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS products");
    }
}
