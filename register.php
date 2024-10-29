<?php
header('Content-Type: text/html; charset=UTF-8');

// Variable to hold the ChatGPT response
$chatgptMessage = '';

// Check if form data is available
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['company_name']) && !empty($_POST['password'])) {
    $company_name = htmlspecialchars($_POST['company_name']);
    $password = htmlspecialchars($_POST['password']);

    // Prepare message for ChatGPT
    $message = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => "A new user has registered with company name: $company_name and password: $password"]
        ]
    ];

    // Make the API call
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer YOUR_API_KEY',  // Replace YOUR_API_KEY with your actual API key
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $responseData = json_decode($response, true);
        $chatgptMessage = $responseData['choices'][0]['message']['content'] ?? 'No response from ChatGPT.';
    } else {
        $chatgptMessage = "Failed to connect to ChatGPT API.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
</head>
<body>
    <h2>Register</h2>
    <form action="" method="POST">
        <label for="company_name">Company Name:</label>
        <input type="text" id="company_name" name="company_name" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit">Register</button>
    </form>

    <?php if (!empty($chatgptMessage)): ?>
        <h2>ChatGPT Response</h2>
        <p><?php echo nl2br(htmlspecialchars($chatgptMessage)); ?></p>
    <?php endif; ?>
</body>
</html>