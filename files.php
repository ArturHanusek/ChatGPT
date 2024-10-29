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

// Handle GET request to list files and folders in a specified folder
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['folder'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing or invalid folder parameter']);
        exit;
    }

    $folderPath = realpath(__DIR__ . '/' . $_GET['folder']);
    if (!$folderPath || !is_dir($folderPath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Folder not found']);
        exit;
    }

    $contents = [];
    foreach (scandir($folderPath) as $item) {
        if ($item === '.' || $item === '..') continue;
        $contents[] = [
            'name' => $item,
            'type' => is_dir($folderPath . '/' . $item) ? 'folder' : 'file'
        ];
    }

    echo json_encode([
        'folder' => $_GET['folder'],
        'contents' => $contents
    ]);
    exit;
}

// Handle POST request for file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid content type']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['files']) || !is_array($data['files'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input format']);
        exit;
    }

    foreach ($data['files'] as $file) {
        if (!isset($file['name']) || !isset($file['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input format']);
            exit;
        }

        $filename = basename($file['name']);
        $filePath = __DIR__ . '/' . $filename;

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
