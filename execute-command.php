<?php
// Include authentication
include 'authenticate.php';

header('Content-Type: application/json');

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['command'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing command in request."]);
    exit;
}

// Sanitize and execute the command with error capture
$command = escapeshellcmd($input['command']) . " 2>&1";
$output = shell_exec($command);

// Treat empty output as success or capture error output
echo json_encode([
    "command" => $command,
    "output" => trim($output) === "" ? "Command executed with no output" : trim($output)
]);
?>