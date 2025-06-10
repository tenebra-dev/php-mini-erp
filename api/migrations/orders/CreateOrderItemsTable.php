<?php
namespace migrations\orders;

use migrations\Migration;

class CreateOrderItemsTable extends Migration {
    public function up() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->execute("
                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    variation_id INTEGER,
                    quantity INTEGER NOT NULL,
                    unit_price REAL NOT NULL,
                    product_name TEXT NOT NULL,
                    variation_name TEXT
                )
            ");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_order_id ON order_items (order_id)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_product_id ON order_items (product_id)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_variation_id ON order_items (variation_id)");
            // Foreign keys podem ser ativadas no SQLite, mas geralmente são ignoradas em testes
        } else {
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
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS order_items");
    }
}
