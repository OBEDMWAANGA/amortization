<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_amount = $_POST["loan_amount"];
    $interest_rate = ($_POST["interest_rate"] / 100); // Convert to decimal
    $monthly_payment = $_POST["monthly_payment"];
    $loan_tenure = $_POST["loan_tenure"]; // User-specified loan tenure

    $month = 1;
    $loan_balance = $loan_amount;
    
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Month</th><th>Interest Payment</th><th>Principal Payment</th><th>Loan Balance</th></tr>";

    while ($loan_balance > 0 && $month <= $loan_tenure) {
        $interest_payment = $loan_balance * ($interest_rate / 12); // Monthly interest payment
        $principal_payment = $monthly_payment - $interest_payment;

        $loan_balance -= $principal_payment;

        // Get the current date 
        $current_date = date("Y-m-d");

        echo "<tr>";
        echo "<td>$current_date</td>";
        echo "<td>$month</td>";
        echo "<td>K" . number_format($interest_payment, 2) . "</td>";
        echo "<td>K" . number_format($principal_payment, 2) . "</td>";
        echo "<td>K" . number_format($loan_balance, 2) . "</td>";
        echo "</tr>";

        $month++;
    }

    echo "</table>";
}
?>
