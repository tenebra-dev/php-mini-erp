<?php
namespace migrations;

abstract class Migration {
    protected $db;
    
    public function __construct(\PDO $db) {
        $this->db = $db;
    }
    
    abstract public function up();
    abstract public function down();
    
    protected function execute($sql) {
        return $this->db->exec($sql);
    }
}
