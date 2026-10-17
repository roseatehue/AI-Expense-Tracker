<?php
session_start();
include 'db_connection.php';

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];

    if ($type === 'expense') {
        $category_id = $_POST['category'];
        // Corrected SQL query to use the 'expense_date' column for expenses
        $sql = "INSERT INTO expenses (user_id, amount, description, expense_date, category_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // The bind_param types remain the same as the data hasn't changed
            $stmt->bind_param("idssi", $user_id, $amount, $description, $date, $category_id);
        }
    } elseif ($type === 'income') {
        // Insert income into the database using the correct 'income_date' column name
        $sql = "INSERT INTO income (user_id, amount, description, income_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Added check to see if prepare was successful
        if ($stmt) {
            $stmt->bind_param("idss", $user_id, $amount, $description, $date);
        }
    }
    
    // Check if a statement object exists before executing
    if ($stmt && $stmt->execute()) {
        // Success
        $stmt->close();
        $conn->close();
        // Redirect back to the index page to prevent form resubmission
        header("Location: index.php");
        exit();
    } else {
        // Error
        echo "Error: " . ($stmt ? $stmt->error : $conn->error);
        if ($stmt) {
            $stmt->close();
        }
        $conn->close();
    }
}
?>
