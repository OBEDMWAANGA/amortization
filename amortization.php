<?php
require('./fpdf/fpdf.php');
require('./ExcelFormulas.php');

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
    $pmt = abs(ExcelFormulas::PMT($interest_rate, $loan_term, $loan_amount));
    $monthly_payment = $pmt;

    $remaining_balance = $loan_term * $monthly_payment;
    $current_date = strtotime("now");
    $pdf = new PDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'Month', 1);
    $pdf->Cell(40, 10, 'Date', 1);
    $pdf->Cell(30, 10, 'Interest', 1);
    $pdf->Cell(30, 10, 'Principal', 1);
    $pdf->Cell(30, 10, 'Installment', 1);    
    $pdf->Cell(40, 10, 'Remaining Balance', 1);
    $pdf->Ln();

    $months = 0;

    while ($months < $loan_term) {
        $months++;
        $payment_date = date("jS F, Y", strtotime("+$months month", $current_date));        
        $monthly_interest = abs(ExcelFormulas::IPMT($loan_amount, $pmt, $interest_rate, $months));
        $monthly_principal =  abs(ExcelFormulas::PPMT($interest_rate, $months, $loan_term, $loan_amount));
        $remaining_balance -= $monthly_payment;

        $pdf->Cell(20, 10, $months, 1);
        $pdf->Cell(40, 10, $payment_date, 1);
        $pdf->Cell(30, 10, 'K' . number_format($monthly_interest, 2), 1);
        $pdf->Cell(30, 10, 'K' . number_format($monthly_principal, 2), 1);
        $pdf->Cell(30, 10, 'K' . number_format($monthly_payment, 2), 1);
        $pdf->Cell(40, 10, 'K' . number_format($remaining_balance, 2), 1);
        $pdf->Ln();
    }

    $pdf->Output('amortization_schedule.pdf', 'I');
}
?>