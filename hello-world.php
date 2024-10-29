<?php
include 'authenticate.php';

header('Content-Type: application/json');

// Check for API key and respond to GET requests with a "Hello, World!" message
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['message' => 'Hello, World!']);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}
