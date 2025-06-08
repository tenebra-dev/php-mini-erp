<?php
namespace migrations\webhooks;

use migrations\Migration;

class CreateWebhookLogsTable extends Migration {
    public function up() {
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
    
    public function down() {
        $this->execute("DROP TABLE IF EXISTS webhook_logs");
    }
}
