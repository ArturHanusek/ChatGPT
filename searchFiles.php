<?php
// Include authentication script
include 'authenticate.php';

// Set response header to JSON
header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Please use POST."]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (!isset($data['directory']) || !is_string($data['directory'])) {
    http_response_code(400);
    echo json_encode(["error" => "Directory path is required and should be a string."]);
    exit;
}

// Set search parameters
$directory = $data['directory'];
$pattern = isset($data['pattern']) ? $data['pattern'] : '*';
$extension = isset($data['extension']) ? strtolower($data['extension']) : null;

// Check if directory exists
if (!is_dir($directory)) {
    http_response_code(404);
    echo json_encode(["error" => "Directory not found."]);
    exit;
}

// Initialize an array to store matching files
$matchingFiles = [];

// Iterate through files in the directory
$files = scandir($directory);
foreach ($files as $file) {
    // Skip '.' and '..'
    if ($file === '.' || $file === '..') continue;
    
    // Check for pattern match
    if (fnmatch($pattern, $file)) {
        // Check for extension match if specified
        if ($extension === null || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === $extension) {
            $matchingFiles[] = $file;
        }
    }
}

// Return the results as JSON
echo json_encode([
    "directory" => $directory,
    "pattern" => $pattern,
    "extension" => $extension,
    "matching_files" => $matchingFiles
]);