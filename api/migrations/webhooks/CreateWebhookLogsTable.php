<?php
namespace migrations\webhooks;

use migrations\Migration;

class CreateWebhookLogsTable extends Migration {
    public function up() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->execute("
                CREATE TABLE IF NOT EXISTS webhook_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    status TEXT NOT NULL,
                    payload TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_order_id ON webhook_logs (order_id)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_created_at ON webhook_logs (created_at)");
            // Foreign keys podem ser ignoradas em SQLite para testes
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS webhook_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    status VARCHAR(50) NOT NULL,
                    payload TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    INDEX idx_order_id (order_id),
                    INDEX idx_created_at (created_at)
                )
            ");
        }
    }
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS webhook_logs");
    }
}
