<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    $statement = $pdo->query(
        "SELECT
            id,
            user_name,
            DATE_FORMAT(reported_at, '%Y-%m-%d %H:%i:%s') AS reported_at,
            user_agent,
            location_name,
            latitude,
            longitude,
            category,
            description
         FROM hazards
         ORDER BY reported_at DESC"
    );

    echo json_encode($statement->fetchAll());
} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve hazards.'
    ]);
}