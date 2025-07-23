<?php
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'dbname' => getenv('DB_DATABASE') ?: 'testdb',
    'username' => getenv('DB_USERNAME') ?: 'testuser',
    'password' => getenv('DB_PASSWORD') ?: 'testpass'
];
