<?php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loan Amortization Calculator</title>
</head>
<body>
    <h1>Loan Amortization Calculator</h1>
    <form method="post" action="amortization.php">
        <label for="loan_amount">Loan Amount:</label>
        <input type="number" name="loan_amount" required><br><br>

        <label for="interest_rate">Interest Rate (%):</label>
        <input type="number" name="interest_rate" step="0.01" required><br><br>

        <label for="loan_term">Loan Term (months):</label>
        <input type="number" name="loan_term" required><br><br>

        <input type="submit" value="Display Amortization Schedule">
    </form>
</body>
</html>



