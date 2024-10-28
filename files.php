<?php
// Load API key from .env file
$env = parse_ini_file(__DIR__ . '/.env');
$apiKey = $env['API_KEY'] ?? null;

// Check API key for every request
if ($_SERVER['HTTP_X_API_KEY'] !== $apiKey) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Invalid API key']);
    exit;
}

// POST request to save files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input) || !isset($input['files']) || !is_array($input['files'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input format']);
        exit;
    }

    $response = [];
    foreach ($input['files'] as $file) {
        if (isset($file['name'], $file['content'])) {
            // Ensure the file path is within the current directory
            $path = realpath(__DIR__) . DIRECTORY_SEPARATOR . basename($file['name']);
            if (strpos(realpath($path), realpath(__DIR__)) !== 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid file path']);
                exit;
            }

            // Save file content
            file_put_contents($path, $file['content']);
            $response[] = ['file' => $file['name'], 'status' => 'saved'];
        } else {
            $response[] = ['file' => $file['name'] ?? '', 'status' => 'failed', 'error' => 'Invalid file format'];
        }
    }

    echo json_encode($response);
    exit;
}

// GET request to display OpenAPI schema
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $schema = [
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'File Upload API',
            'version' => '1.0.0',
            'description' => 'API for uploading files with API key protection.'
        ],
        'servers' => [
            ['url' => ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']]
        ],
        'paths' => [
            '/files' => [
                'post' => [
                    'summary' => 'Upload files',
                    'description' => 'Uploads files to the server in specified relative paths.',
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
                                                    'content' => ['type' => 'string']
                                                ],
                                                'required' => ['name', 'content']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Files uploaded successfully.',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'file' => ['type' => 'string'],
                                            'status' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        '403' => [
                            'description' => 'Forbidden: Invalid API key'
                        ],
                        '400' => [
                            'description' => 'Invalid input format'
                        ]
                    ]
                ]
            ]
        ]
    ];

    echo json_encode($schema);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
