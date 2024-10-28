<?php
// File: install.php

// Path to the .env file
$envFile = '.env';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the API key from the form input
    $apiKey = $_POST['api_key'] ?? '';

    // Validate the API key input
    if (!empty($apiKey)) {
        // Write only the API key to the .env file
        file_put_contents($envFile, $apiKey);
        $message = "API key saved successfully!";
    } else {
        $message = "Please enter a valid API key.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Setup</title>
</head>
<body>
    <h2>Enter Your API Key</h2>
    <?php if (isset($message)) echo "<p>$message</p>"; ?>
    
    <form action="" method="POST">
        <label for="api_key">API Key:</label>
        <input type="text" id="api_key" name="api_key" required>
        <button type="submit">Save API Key</button>
    </form>
</body>
</html>
