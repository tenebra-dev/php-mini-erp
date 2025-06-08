<?php
namespace migrations\orders;

use migrations\Migration;

class CreateOrderItemsTable extends Migration {
    public function up() {
        $this->execute("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                variation_id INT,
                quantity INT NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                product_name VARCHAR(100) NOT NULL,
                variation_name VARCHAR(100),
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id),
                FOREIGN KEY (variation_id) REFERENCES product_variations(id),
                INDEX idx_order_id (order_id),
                INDEX idx_product_id (product_id),
                INDEX idx_variation_id (variation_id)
            )
        ");
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS order_items");
    }
}
