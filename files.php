<?php
// Load API key from .env file
$envContent = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$apiKey = '';
foreach ($envContent as $line) {
    if (strpos(trim($line), 'API_KEY=') === 0) {
        $apiKey = trim(explode('=', $line, 2)[1]);
        break;
    }
}

// Check if API key is provided and valid
$headers = getallheaders();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== "Bearer $apiKey") {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Invalid API key']);
    exit;
}

// Verify JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['files']) || !is_array($data['files'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input format']);
    exit;
}

foreach ($data['files'] as $file) {
    if (!isset($file['name']) || !isset($file['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file structure']);
        exit;
    }

    // Ensure filename does not attempt directory traversal
    $filePath = __DIR__ . '/' . basename($file['name']);

    // Save file content to the local file system
    file_put_contents($filePath, $file['content']);
}

http_response_code(200);
echo json_encode(['message' => 'Files saved successfully']);
?>
