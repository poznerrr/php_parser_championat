<?php
declare(strict_types=1);

return ['host' => 'mysql:host=localhost:3306;dbname=mvc;charset=utf8mb4',
    'user' => 'root',
    'pass' => 'mysql',
    'opts' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

