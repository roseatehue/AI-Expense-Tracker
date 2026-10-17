<?php
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    return [
        'total_income' => 0,
        'total_expenses' => 0,
        'remaining_budget' => 0,
        'categorized_spending' => []
    ];
}
$user_id = $_SESSION['user_id'];

$summary = [
    'total_income' => 0,
    'total_expenses' => 0,
    'remaining_budget' => 0,
    'categorized_spending' => []
];

$sql_income = "SELECT SUM(amount) AS total_income FROM income WHERE user_id = ?";
$stmt_income = $conn->prepare($sql_income);
$stmt_income->bind_param("i", $user_id);
$stmt_income->execute();
$result_income = $stmt_income->get_result();
if ($result_income->num_rows > 0) {
    $row = $result_income->fetch_assoc();
    $summary['total_income'] = $row['total_income'] ? $row['total_income'] : 0;
}
$stmt_income->close();

$sql_expenses = "SELECT c.category_name, SUM(e.amount) AS total_amount
                 FROM expenses e
                 JOIN categories c ON e.category_id = c.category_id
                 WHERE e.user_id = ?
                 GROUP BY c.category_name";
$stmt_expenses = $conn->prepare($sql_expenses);
$stmt_expenses->bind_param("i", $user_id);
$stmt_expenses->execute();
$result_expenses = $stmt_expenses->get_result();
if ($result_expenses->num_rows > 0) {
    while ($row = $result_expenses->fetch_assoc()) {
        $summary['categorized_spending'][$row['category_name']] = $row['total_amount'];
        $summary['total_expenses'] += $row['total_amount'];
    }
}
$stmt_expenses->close();

$summary['remaining_budget'] = $summary['total_income'] - $summary['total_expenses'];
$conn->close();
return $summary;
?>