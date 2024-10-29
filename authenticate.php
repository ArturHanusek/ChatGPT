<?php

header("Content-Type: application/json");

// Load API key from .env file
$dotenvPath = __DIR__ . '/../.env';
if (!file_exists($dotenvPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error: Missing .env file.']);
    exit;
}

// Read the API key from the .env file
$apiKey = trim(file_get_contents($dotenvPath));

// Check API key in the Authorization header
$headers = getallheaders();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer ' . $apiKey) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Invalid API key']);
    exit;
}

?>