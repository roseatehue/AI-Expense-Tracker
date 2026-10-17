<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password for security
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql_check = "SELECT user_id FROM users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("Location: register.php?error=username_exists");
        exit();
    }
    $stmt_check->close();

    $sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password_hash);

    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        $default_categories = ['Food', 'Transportation', 'Rent', 'Utilities', 'Entertainment', 'Shopping'];

        $sql_categories = "INSERT INTO categories (category_name, user_id) VALUES (?, ?)";
        $stmt_categories = $conn->prepare($sql_categories);
        
        foreach ($default_categories as $category_name) {
            $stmt_categories->bind_param("si", $category_name, $new_user_id);
            $stmt_categories->execute();
        }
        $stmt_categories->close();

        header("Location: login.php?success=registered");
    } else {
        header("Location: register.php?error=registration_failed");
    }
    $stmt->close();
}
$conn->close();
?>