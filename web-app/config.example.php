<?php

$host = 'localhost';
$dbName = 'hazardone';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);

    exit;
}