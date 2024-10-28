<?php
// files.php

// Load API key from .env file
$dotenvPath = __DIR__ . '/.env';
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

// Validate request content-type
if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid content type']);
    exit;
}

// Retrieve and decode JSON body
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['files']) || !is_array($data['files'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input format']);
    exit;
}

// Save each file to its specified relative location
foreach ($data['files'] as $file) {
    if (!isset($file['name']) || !isset($file['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input format']);
        exit;
    }

    // Ensure filename does not navigate up the directory structure
    $filename = basename($file['name']);
    $filePath = __DIR__ . '/' . $filename;

    // Save file content
    if (file_put_contents($filePath, $file['content']) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file: ' . $filename]);
        exit;
    }
}

http_response_code(200);
echo json_encode(['message' => 'Files saved successfully']);
