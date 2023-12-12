<?php
require('./fpdf/fpdf.php');
require('./ExcelFormulas.php');

class PDF extends FPDF
{
    private $loan_amount;
    private $interest_rate;
    private $loan_term;
    private $monthly_payment;
    private $total_interest;

    function setLoanDetails($loan_amount, $interest_rate, $loan_term, $monthly_payment, $total_interest)
    {
        $this->loan_amount = $loan_amount;
        $this->interest_rate = $interest_rate;
        $this->loan_term = $loan_term;
        $this->monthly_payment = $monthly_payment;
        $this->total_interest = $total_interest;
    }

    function header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Amortization Schedule', 0, 1, 'C');
        $this->Ln(10);

        // Summary Table
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 10, 'Number of Months:', 0);
        $this->Cell(30, 10, $this->loan_term, 0);
        $this->Cell(40, 10, 'Interest Rate:', 0);
        $this->Cell(30, 10, number_format($this->interest_rate * 100, 2) . '%', 0);
        $this->Ln();

        $this->Cell(40, 10, 'Principal Amount:', 0);
        $this->Cell(30, 10, 'K' . number_format($this->loan_amount, 2), 0);
        $this->Cell(40, 10, 'Total Interest Amount:', 0);
        $this->Cell(30, 10, 'K' . number_format($this->total_interest, 2), 0);
        $this->Ln();

        $this->Cell(40, 10, 'Total Repayment:', 0);
        $this->Cell(30, 10, 'K' . number_format($this->monthly_payment * $this->loan_term, 2), 0);
        $this->Ln(15);

        // Amortization Schedule Table Headers
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 10, 'Month', 1);
        $this->Cell(40, 10, 'Date', 1);
        $this->Cell(30, 10, 'Interest', 1);
        $this->Cell(30, 10, 'Principal', 1);
        $this->Cell(30, 10, 'Installment', 1);
        $this->Cell(40, 10, 'Remaining Balance', 1);
        $this->Ln();
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
    $total_interest = 0;

    // Initial while loop to calculate total interest
    $months = 0;
    while ($months < $loan_term) {
        $months++;
        $monthly_principal = abs(ExcelFormulas::PPMT($interest_rate, $months, $loan_term, $loan_amount));
        $total_interest += $monthly_payment - $monthly_principal;
    }

    $pdf->setLoanDetails($loan_amount, $interest_rate, $loan_term, $monthly_payment, $total_interest);
    $pdf->AddPage();

    for ($months = 1; $months <= $loan_term; $months++) {
        $payment_date = date("jS F, Y", strtotime("+$months month", $current_date));
        $monthly_principal = abs(ExcelFormulas::PPMT($interest_rate, $months, $loan_term, $loan_amount));
        $monthly_interest = $monthly_payment - $monthly_principal;
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
