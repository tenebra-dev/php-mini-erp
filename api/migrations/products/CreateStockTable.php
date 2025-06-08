<?php
namespace migrations\products;

use migrations\Migration;

class CreateStockTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS stock (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                variation_id INT,
                quantity INT NOT NULL DEFAULT 0,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE CASCADE,
                INDEX idx_product_id (product_id),
                INDEX idx_variation_id (variation_id)
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS stock");
    }
}
