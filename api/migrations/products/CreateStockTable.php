<?php
namespace migrations\products;

use migrations\Migration;

class CreateStockTable extends Migration {
    public function up() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->execute("
                CREATE TABLE IF NOT EXISTS stock (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    variation_id INTEGER,
                    quantity INTEGER NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_product_id ON stock (product_id)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_variation_id ON stock (variation_id)");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS stock (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_id INT NOT NULL,
                    variation_id INT NULL,
                    quantity INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_product_id (product_id),
                    INDEX idx_variation_id (variation_id)
                )
            ");
        }
    }

    public function down() {
        $this->execute("DROP TABLE IF EXISTS stock");
    }
}
