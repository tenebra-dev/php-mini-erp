<?php
namespace migrations;

class MigrationRunner {
    private $db;
    
    public function __construct(\PDO $db) {
        $this->db = $db;
    }
    
    public function run() {
        $migrations = $this->getMigrationClasses();
        
        foreach ($migrations as $migrationClass) {
            $migration = new $migrationClass($this->db);
            $migration->up();
        }
    }
    
    protected function getMigrationClasses() {
        return [
            // Products
            'migrations\products\CreateProductsTable',
            'migrations\products\CreateProductVariationsTable',
            'migrations\products\CreateStockTable',
            
            // Coupons
            'migrations\coupons\CreateCouponsTable',
            
            // Orders
            'migrations\orders\CreateOrdersTable',
            'migrations\orders\CreateOrderItemsTable',
            
            // Webhooks
            'migrations\webhooks\CreateWebhookLogsTable'
        ];
    }
}
