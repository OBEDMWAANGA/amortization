<!DOCTYPE html>
<html>
<head>
    <title>Amortization Schedule</title>
</head>
<body>
    <h1>Amortization Schedule</h1>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $loan_amount = $_POST["loan_amount"];
        $interest_rate = $_POST["interest_rate"] / 100; // Convert interest rate to decimal
        $loan_term = $_POST["loan_term"];

        // Calculate monthly interest rate
        $monthly_interest_rate = $interest_rate / 12;

        // Calculate monthly payment using the amortization formula
        $monthly_payment = ($loan_amount * $monthly_interest_rate) / (1 - (1 + $monthly_interest_rate)**(-$loan_term));

        // Initialize variables for the loop
        $months = 0;

        echo "<table border='1'>
                <tr>
                    <th>Month</th>
                    <th>Date</th>
                    <th>Interest</th>
                    <th>Principal</th>
                    <th>Remaining Balance</th>
                </tr>";

        $remaining_balance = $loan_amount;

        // Get the current date
        $current_date = strtotime("now");

        while ($months < $loan_term) {
            $months++;
            $monthly_interest = $remaining_balance * $monthly_interest_rate;
            $monthly_principal = $monthly_payment - $monthly_interest;
            $remaining_balance -= $monthly_principal;

            // payment date (current date + $months months)
            $payment_date = date("jS F, Y", strtotime("+$months months", $current_date));

            echo "<tr>
                    <td>$months</td>
                    <td>$payment_date</td>
                    <td>K" . number_format($monthly_interest, 2) . "</td>
                    <td>K" . number_format($monthly_principal, 2) . "</td>
                    <td>K" . number_format($remaining_balance, 2) . "</td>
                  </tr>";
        }

        echo "</table>"; 
    }
    ?>
    
    <br><button> <a  href="index.html"> Back </a></button>
</body>
</html>
