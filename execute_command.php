<?php
// Define the API key directly from the .env file.
$envFilePath = __DIR__ . '/.env';
$apiKey = file_exists($envFilePath) ? trim(file_get_contents($envFilePath)) : '';

// Check if the API key exists in the .env file.
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["error" => "API key not configured."]);
    exit();
}

// Set headers for JSON response.
header('Content-Type: application/json');

// Retrieve and validate the provided API key.
$headers = getallheaders();
$providedApiKey = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($providedApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access. Invalid API key."]);
    exit();
}

// Retrieve and sanitize the command from the POST request.
$input = json_decode(file_get_contents('php://input'), true);
$command = isset($input['command']) ? escapeshellcmd($input['command']) : null;

if (empty($command)) {
    http_response_code(400);
    echo json_encode(["error" => "No command provided."]);
    exit();
}

// Execute the command and capture the output.
exec($command . ' 2>&1', $output, $statusCode);

// Return the output and status as JSON.
$response = [
    "command" => $command,
    "output" => $output,
    "status" => $statusCode
];

http_response_code(200);
echo json_encode($response);
?>