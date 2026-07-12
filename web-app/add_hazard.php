<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$requiredFields = [
    'user_name',
    'user_agent',
    'location_name',
    'latitude',
    'longitude',
    'category',
    'description'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => "Missing field: $field"
        ]);

        exit;
    }
}

$allowedCategories = ['Road', 'Environmental', 'Building'];

if (!in_array($data['category'], $allowedCategories, true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid hazard category.'
    ]);

    exit;
}

if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid GPS coordinates.'
    ]);

    exit;
}

try {
    $statement = $pdo->prepare(
        "INSERT INTO hazards (
            user_name,
            user_agent,
            location_name,
            latitude,
            longitude,
            category,
            description
        ) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $statement->execute([
        trim($data['user_name']),
        trim($data['user_agent']),
        trim($data['location_name']),
        $data['latitude'],
        $data['longitude'],
        $data['category'],
        trim($data['description'])
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Hazard reported successfully.',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to save the hazard.'
    ]);
}