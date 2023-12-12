<?php

class ExcelFormulas {
    /**
     * PVIF - Present Value Interest Factor
     *
     * @param float $rate - Interest rate per period
     * @param int $nper - Number of periods
     * @return float
     */
    public static function PVIF($rate, $nper) {
        return pow(1 + $rate, $nper);
    }

    /**
     * FVIFA - Future Value Interest Factor of Annuity
     *
     * @param float $rate - Interest rate per period
     * @param int $nper - Number of periods
     * @return float
     */
    public static function FVIFA($rate, $nper) {
        return ($rate == 0) ? $nper : (self::PVIF($rate, $nper) - 1) / $rate;
    }

    /**
     * PMT - Payment
     *
     * @param float $rate - Interest rate per period
     * @param int $nper - Number of periods
     * @param float $pv - Present value (initial investment)
     * @param float $fv - Future value (default: 0)
     * @param int $type - Payment type (0 = end of period, 1 = beginning of period, default: 0)
     * @return float
     */
    public static function PMT($rate, $nper, $pv, $fv = 0, $type = 0) {
        if (!$fv) $fv = 0;
        if (!$type) $type = 0;

        if ($rate == 0) return -($pv + $fv) / $nper;

        $pvif = pow(1 + $rate, $nper);
        $pmt = $rate / ($pvif - 1) * -($pv * $pvif + $fv);

        if ($type == 1) {
            $pmt /= (1 + $rate);
        }

        return $pmt;
    }

    /**
     * IPMT - Interest Payment
     *
     * @param float $pv - Present value (initial investment)
     * @param float $pmt - Payment
     * @param float $rate - Interest rate per period
     * @param int $per - Period number
     * @return float
     */
    public static function IPMT($pv, $pmt, $rate, $per) {
        $tmp = pow(1 + $rate, $per - 1);
        return 0 - ($pv * $tmp * $rate + $pmt * ($tmp - 1));
    }

    /**
     * PPMT - Principal Payment
     *
     * @param float $rate - Interest rate per period
     * @param int $per - Period number
     * @param int $nper - Number of periods
     * @param float $pv - Present value (initial investment)
     * @param float $fv - Future value (default: 0)
     * @param int $type - Payment type (0 = end of period, 1 = beginning of period, default: 0)
     * @return float|null
     */
    public static function PPMT($rate, $per, $nper, $pv, $fv = 0, $type = 0) {
        if ($per < 1 || ($per >= $nper + 1)) return null;
        $pmt = self::PMT($rate, $nper, $pv, $fv, $type);
        $ipmt = self::IPMT($pv, $pmt, $rate, $per);
        return $pmt - $ipmt;
    }

    /**
     * DaysBetween - Calculate the number of days between two dates
     *
     * @param string $date1 - First date (YYYY-MM-DD)
     * @param string $date2 - Second date (YYYY-MM-DD)
     * @return int
     */
    public static function DaysBetween($date1, $date2) {
        $oneDay = 24 * 60 * 60 * 1000;
        return round(abs((strtotime($date1) - strtotime($date2)) / $oneDay));
    }

    /**
     * XNPV - Net Present Value
     *
     * @param float $rate - Discount rate per period
     * @param array $values - Array of associative arrays with 'Date' and 'Flow' keys
     * @return float
     */
    public static function XNPV($rate, $values) {
        $xnpv = 0.0;
        $firstDate = strtotime($values[0]['Date']);
        foreach ($values as $tmp) {
            $value = $tmp['Flow'];
            $date = strtotime($tmp['Date']);
            $xnpv += $value / pow(1 + $rate, self::DaysBetween(date('Y-m-d', $firstDate), date('Y-m-d', $date)) / 365);
        }
        return $xnpv;
    }

    /**
     * XIRR - Internal Rate of Return
     *
     * @param array $values - Array of associative arrays with 'Date' and 'Flow' keys
     * @param float $guess - Initial guess for the rate (default: 0.1)
     * @return float|null
     */
    public static function XIRR($values, $guess = 0.1) {
        if (!$guess) $guess = 0.1;

        $x1 = 0.0;
        $x2 = $guess;
        $f1 = self::XNPV($x1, $values);
        $f2 = self::XNPV($x2, $values);

        for ($i = 0; $i < 100; $i++) {
            if (($f1 * $f2) < 0.0) break;
            if (abs($f1) < abs($f2)) {
                $f1 = self::XNPV($x1 += 1.6 * ($x1 - $x2), $values);
            } else {
                $f2 = self::XNPV($x2 += 1.6 * ($x2 - $x1), $values);
            }
        }

        if (($f1 * $f2) > 0.0) return null;

        $f = self::XNPV($x1, $values);
        if ($f < 0.0) {
            $rtb = $x1;
            $dx = $x2 - $x1;
        } else {
            $rtb = $x2;
            $dx = $x1 - $x2;
        }

        for ($i = 0; $i < 100; $i++) {
            $dx *= 0.5;
            $x_mid = $rtb + $dx;
            $f_mid = self::XNPV($x_mid, $values);
            if ($f_mid <= 0.0) $rtb = $x_mid;
            if ((abs($f_mid) < 1.0e-6) || (abs($dx) < 1.0e-6)) return $x_mid;
        }

        return null;
    }
}

// $pmt = ExcelFormulas::PMT(0.25, 12, 1000);
// echo ExcelFormulas::IPMT(1000, $pmt, 0.25, 1);
// echo "\n";
// echo ExcelFormulas::PPMT(0.25, 1, 12, 1000);

?>
