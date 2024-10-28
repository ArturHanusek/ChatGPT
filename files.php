<?php
// files.php

header("Content-Type: application/json");

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

// Handle GET request for file retrieval
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing file parameter']);
        exit;
    }

    // Sanitize the file parameter to prevent directory traversal
    $filename = basename($_GET['file']);
    $filePath = __DIR__ . '/' . $filename;

    // Check if the file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }

    // Read and return the file content
    $fileContent = file_get_contents($filePath);
    echo json_encode(['file' => $filename, 'content' => $fileContent]);
    exit;
}

// Handle POST request for file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
