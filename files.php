<?php
// Define your API key
define('API_KEY', 'YOUR_API_KEY_HERE');

// Set the directory where files should be saved
define('BASE_DIR', __DIR__ . '/files/');

// Check API key
function validateApiKey($providedKey) {
    return $providedKey === API_KEY;
}

// Save file function
function saveFile($filePath, $content) {
    $fullPath = BASE_DIR . $filePath;
    $directory = dirname($fullPath);

    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Save file content
    return file_put_contents($fullPath, $content) !== false;
}

// Handle incoming request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for API key in the headers
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || !validateApiKey(str_replace('Bearer ', '', $headers['Authorization']))) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid API key.']);
        exit;
    }

    // Get JSON data from the request body
    $inputData = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON.']);
        exit;
    }

    $errors = [];
    foreach ($inputData as $file) {
        if (isset($file['name'], $file['content'])) {
            if (!saveFile($file['name'], $file['content'])) {
                $errors[] = $file['name'];
            }
        } else {
            $errors[] = $file['name'] ?? 'unknown';
        }
    }

    if (empty($errors)) {
        echo json_encode(['status' => 'success', 'message' => 'All files saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Some files could not be saved.', 'errors' => $errors]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}