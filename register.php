<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Expense Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 300px; }
        h1 { color: #333; text-align: center; }
        label, input { display: block; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
        input[type="submit"] { background-color: #5cb85c; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
        input[type="submit"]:hover { background-color: #4cae4c; }
        .message { text-align: center; margin-top: 10px; }
        .message.error { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h1>Register</h1>
    <?php if (isset($_GET['error'])): ?>
        <p class="message error">Registration failed. Username might already exist.</p>
    <?php endif; ?>
    <form action="register_handler.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" value="Register">
    </form>
    <div class="message">
        <p>Already have an account? <a href="login.php">Log in here</a></p>
    </div>
</div>

</body>
</html>