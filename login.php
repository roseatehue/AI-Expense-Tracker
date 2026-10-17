<?php
// Start the session at the very beginning of the file.
session_start();

// Include the database connection file.
include 'db_connection.php';

// If the user is already logged in, redirect them to the index page immediately.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

// Check if the form was submitted using the POST method.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare a statement to check the user credentials.
    // We use a prepared statement to prevent SQL injection.
    $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if the statement was prepared successfully.
    if ($stmt === false) {
        $error_message = "Database error: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with that username was found.
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify the entered password against the hashed password from the database.
            if (password_verify($password, $user['password_hash'])) {
                // Login successful. Set session variables to keep the user logged in.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect the user to the main index page.
                header("Location: index.php");
                exit();
            } else {
                // Password does not match.
                $error_message = "Invalid username or password.";
            }
        } else {
            // No user was found with that username.
            $error_message = "Invalid username or password.";
        }
        $stmt->close();
    }
}

// Close the database connection.
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expense Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 300px; }
        h1 { color: #333; text-align: center; }
        label, input { display: block; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
        input[type="text"], input[type="password"] { padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="submit"] { background-color: #007bff; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .message { text-align: center; margin-top: 10px; }
        .message.error { color: red; }
        .message.success { color: green; }
    </style>
</head>
<body>

<div class="container">
    <h1>Login</h1>
    <?php if ($error_message): ?>
        <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <p class="message success">Registration successful! Please log in.</p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" value="Log In">
    </form>
    <div class="message">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

</body>
</html>
