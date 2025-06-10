<?php
namespace migrations\products;

use migrations\Migration;

class CreateProductVariationsTable extends Migration {
    public function up() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->execute("
                CREATE TABLE IF NOT EXISTS product_variations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    variation_name TEXT NOT NULL,
                    variation_value TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_product_id ON product_variations (product_id)");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS product_variations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_id INT NOT NULL,
                    variation_name VARCHAR(100) NOT NULL,
                    variation_value VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_product_id (product_id)
                )
            ");
        }
    }

    public function down() {
        $this->execute("DROP TABLE IF EXISTS product_variations");
    }
}
