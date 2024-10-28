<?php
// Load .env file for API key
$env = parse_ini_file('.env');
$API_KEY = $env['API_KEY'] ?? '';

header('Content-Type: application/json');

function verifyApiKey($key) {
    global $API_KEY;
    return hash_equals($API_KEY, $key);
}

function saveFile($filePath, $content) {
    // Ensure path doesn't climb directory tree
    $realPath = realpath(__DIR__) . '/' . ltrim($filePath, '/');
    if (strpos(realpath($realPath), realpath(__DIR__)) !== 0) {
        throw new Exception("Invalid file path.");
    }
    
    // Create directory if not exists
    $dir = dirname($realPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    file_put_contents($realPath, $content);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate API key
    $headers = getallheaders();
    if (empty($headers['Authorization']) || !verifyApiKey(str_replace('Bearer ', '', $headers['Authorization']))) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Decode JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['files']) || !is_array($data['files'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON format']);
        exit;
    }

    // Save files
    try {
        foreach ($data['files'] as $file) {
            if (!isset($file['name']) || !isset($file['content'])) {
                throw new Exception("Each file requires a 'name' and 'content' field.");
            }
            saveFile($file['name'], $file['content']);
        }
        echo json_encode(['status' => 'Files saved successfully']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Display OpenAPI schema
    $openApiSchema = [
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'File Storage API',
            'version' => '1.0.0',
            'description' => 'API for securely saving files',
        ],
        'paths' => [
            '/' => [
                'post' => [
                    'summary' => 'Save files',
                    'description' => 'Accepts file data and saves it locally.',
                    'parameters' => [
                        [
                            'name' => 'Authorization',
                            'in' => 'header',
                            'required' => true,
                            'schema' => [
                                'type' => 'string',
                            ],
                            'description' => 'Bearer token for API access',
                        ]
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'files' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'name' => ['type' => 'string'],
                                                    'content' => ['type' => 'string'],
                                                ],
                                                'required' => ['name', 'content'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Files saved successfully',
                        ],
                        '400' => [
                            'description' => 'Invalid request data',
                        ],
                        '401' => [
                            'description' => 'Unauthorized',
                        ],
                    ],
                ],
                'get' => [
                    'summary' => 'OpenAPI schema',
                    'description' => 'Returns the OpenAPI schema for the API.',
                    'responses' => [
                        '200' => [
                            'description' => 'OpenAPI schema in JSON format',
                        ],
                    ],
                ],
            ],
        ],
    ];

    echo json_encode($openApiSchema, JSON_PRETTY_PRINT);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
