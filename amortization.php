<?php
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    function header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Amortization Schedule', 0, 1, 'C');
        $this->Ln(10);
    }

    function footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_amount = $_POST["loan_amount"];
    $interest_rate = $_POST["interest_rate"] / 100;
    $loan_term = $_POST["loan_term"];

    $monthly_interest_rate = $interest_rate / 12;
    $monthly_payment = ($loan_amount * $monthly_interest_rate) / (1 - (1 + $monthly_interest_rate)**(-$loan_term));

    $remaining_balance = $loan_amount;
    $current_date = strtotime("now");

    $pdf = new PDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'Month', 1);
    $pdf->Cell(40, 10, 'Date', 1);
    $pdf->Cell(30, 10, 'Interest', 1);
    $pdf->Cell(30, 10, 'Principal', 1);
    $pdf->Cell(40, 10, 'Remaining Balance', 1);
    $pdf->Ln();

    $months = 0;

    while ($months < $loan_term) {
        $months++;
        $monthly_interest = $remaining_balance * $monthly_interest_rate;
        $monthly_principal = $monthly_payment - $monthly_interest;
        $remaining_balance -= $monthly_principal;

        $payment_date = date("jS F, Y", strtotime("+$months months", $current_date));

        $pdf->Cell(20, 10, $months, 1);
        $pdf->Cell(40, 10, $payment_date, 1);
        $pdf->Cell(30, 10, 'K' . number_format($monthly_interest, 2), 1);
        $pdf->Cell(30, 10, 'K' . number_format($monthly_principal, 2), 1);
        $pdf->Cell(40, 10, 'K' . number_format($remaining_balance, 2), 1);
        $pdf->Ln();
    }

    $pdf->Output('amortization_schedule.pdf', 'I');
}
?>
