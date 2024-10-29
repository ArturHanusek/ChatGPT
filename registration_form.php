<?php

header('Content-Type: application/json');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $errors = [];

    // Basic validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (count($errors) === 0) {
        // Hash password for storage
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Example of registration success message (replace with database logic as needed)
        echo json_encode([
            'message' => 'Registration successful',
            'user' => [
                'username' => $username,
                'email' => $email,
            ],
        ]);
    } else {
        // Output validation errors
        echo json_encode(['errors' => $errors]);
    }

    exit;
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
    <form action="registration_form.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>

        <button type="submit">Register</button>
    </form>
</body>
</html>