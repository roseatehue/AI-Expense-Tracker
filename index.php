<?php

session_start();
include 'db_connection.php';
include 'ai_advice.php';


// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Expense Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1, h2 { color: #333; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        form { margin-bottom: 20px; }
        label, input, select, button { display: block; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
        input[type="submit"], .logout-button { background-color: #5cb85c; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 5px; }
        input[type="submit"]:hover { background-color: #4cae4c; }
        .logout-button { background-color: #d9534f; text-decoration: none; text-align: center; }
        .logout-button:hover { background-color: #c9302c; }
        .advice { border-left: 5px solid #007bff; background-color: #e9f5ff; padding: 15px; margin-top: 20px; border-radius: 5px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <a href="logout.php" class="logout-button">Logout</a>
    </header>

    <h2>Add Expense</h2>
    <form action="save_data.php" method="post">
        <input type="hidden" name="type" value="expense">
        <label for="expense_amount">Amount:</label>
        <input type="number" id="expense_amount" name="amount" step="0.01" required>

        <label for="expense_description">Description:</label>
        <input type="text" id="expense_description" name="description" required>

        <label for="expense_date">Date:</label>
        <input type="date" id="expense_date" name="date" required>

        <label for="expense_category">Category:</label>
        <select id="expense_category" name="category" required>
            <?php
            $sql = "SELECT category_id, category_name FROM categories WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["category_id"] . "'>" . htmlspecialchars($row["category_name"]) . "</option>";
                }
            }
            $stmt->close();
            ?>
        </select>
        
        <input type="submit" value="Add Expense">
    </form>

    <hr>

    <h2>Add Income</h2>
    <form action="save_data.php" method="post">
        <input type="hidden" name="type" value="income">
        <label for="income_amount">Amount:</label>
        <input type="number" id="income_amount" name="amount" step="0.01" required>

        <label for="income_description">Description:</label>
        <input type="text" id="income_description" name="description" required>

        <label for="income_date">Date:</label>
        <input type="date" id="income_date" name="date" required>
        
        <input type="submit" value="Add Income">
    </form>

    <hr>
    
    <h2>Add New Category</h2>
    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'category_added') {
            echo '<p style="color: green;">Category added successfully!</p>';
        } elseif ($_GET['status'] == 'category_failed') {
            echo '<p style="color: red;">Failed to add category.</p>';
        }
    }
    ?>
    <form action="add_category.php" method="post">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <input type="submit" value="Add Category">
    </form>

    <hr>

    <h2>Financial Summary</h2>
    <?php
    $summary = include 'get_summary.php';
    include 'ai_advice.php';
    $ai_advice = get_ai_advice_gemini($summary);
    ?>

    <p><strong>Total Income:</strong> $<?php echo number_format($summary['total_income'], 2); ?></p>
    <p><strong>Total Expenses:</strong> $<?php echo number_format($summary['total_expenses'], 2); ?></p>
    <p><strong>Remaining Budget:</strong> $<?php echo number_format($summary['remaining_budget'], 2); ?></p>

    <h3>Spending by Category</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount Spent</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($summary['categorized_spending'] as $category => $amount) {
                echo "<tr><td>" . htmlspecialchars($category) . "</td><td>$" . number_format($amount, 2) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="advice">
        <h3>AI Budget Advice</h3>
        <p><?php echo $ai_advice; ?></p>
    </div>
</div>

</body>
</html>