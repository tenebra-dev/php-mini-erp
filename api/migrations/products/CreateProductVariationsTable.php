<?php
namespace migrations\products;

use migrations\Migration;

class CreateProductVariationsTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS product_variations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                variation_name VARCHAR(255) NOT NULL,
                variation_value VARCHAR(255) NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                INDEX idx_product_id (product_id)
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS product_variations");
    }
}
