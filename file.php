<?php
// Include authentication
include 'authenticate.php';

// Set the header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if 'file' parameter is provided
    if (empty($_GET['file'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing file parameter"]);
        exit;
    }

    $file = $_GET['file'];
    
    // Check if file exists
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(["error" => "File not found"]);
        exit;
    }

    // Retrieve file content
    $content = file_get_contents($file);

    // Return the file content as JSON
    echo json_encode([
        "file" => $file,
        "content" => $content
    ]);
} else {
    // If method is not GET, return 405 Method Not Allowed
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}
