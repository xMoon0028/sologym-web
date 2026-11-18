<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\PayrollPeriod;
use App\Services\LoanService;
use App\Services\PayrollService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollPeriodExport implements FromArray, WithColumnWidths, WithColumnFormatting, WithStyles, WithTitle
{
    protected Collection $payslips;
    protected array $departmentHeaderRows = [];
    protected array $subtotalRows = [];
    protected array $employeeRows = [];
    protected int $summaryTitleRow = 0;
    protected int $summaryTotalRow = 0;
    protected int $totalRowCount = 0;

    public function __construct(protected PayrollPeriod $period)
    {
        $this->payslips = app(PayrollService::class)->getSortedPayslipsForPeriod($this->period);
        $this->payslips->loadMissing(['employee.compensation', 'employee.department', 'customItems']);
    }

    public function title(): string
    {
        return substr('Payroll - ' . $this->period->name, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A'  => 3.5,  // Row #
            'B'  => 20.0, // EMPLOYEE'S NAME / SUPPORT SERVICES / Department
            'C'  => 9.5,  // Salary & Allowances
            'D'  => 8.5,  // Transpo Allowance
            'E'  => 8.0,  // Meal Allowance
            'F'  => 9.5,  // Total Compensation
            'G'  => 7.5,  // Absences
            'H'  => 7.5,  // Late
            'I'  => 8.0,  // Phil Health
            'J'  => 7.5,  // HDMF
            'K'  => 8.0,  // HDMF Loan
            'L'  => 8.0,  // SSS Loan
            'M'  => 8.0,  // Witholding Tax
            'N'  => 9.0,  // Total Deductions
            'O'  => 9.5,  // Net Pay
            'P'  => 2.0,  // Spacer column
            'Q'  => 10.0, // Finance Ref Name
            'R'  => 9.0,  // BASIC
            'S'  => 7.5,  // PER DAY (Basic)
            'T'  => 7.0,  // PER HOUR (Basic)
            'U'  => 6.5,  // PER MIN (Basic)
            'V'  => 8.5,  // Allowance
            'W'  => 7.5,  // PER DAY (Allowance)
            'X'  => 9.0,  // TOTAL COMP
            'Y'  => 7.5,  // daily with allowance
            'Z'  => 7.5,  // Current TC
            'AA' => 9.0,  // Current/day with allowance
            'AB' => 7.5,  // New TC 4/1/25
            'AC' => 8.0,  // PER DAY with Allowance
            'AD' => 7.5,  // PER HOUR with Allowance
            'AE' => 7.0,  // PER MIN with Allowance
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'N'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'O'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'R'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'S'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'T'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'U'  => '0.0000',
            'V'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'W'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'X'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AA' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AC' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AD' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'AE' => '0.0000',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $blankRow = array_fill(0, 31, '');

        $startStr = $this->period->start_date ? $this->period->start_date->format('M j') : '';
        $endStr = $this->period->end_date ? $this->period->end_date->format('M j, Y') : '';
        $cutoffText = ($startStr && $endStr) ? "{$startStr}-{$endStr}" : $this->period->name;
        $periodRangeText = ($startStr && $endStr) ? "{$startStr} - {$endStr}" : $this->period->name;
        $paymentDateText = $this->period->date_of_payment
            ? $this->period->date_of_payment->format('F j, Y')
            : ($this->period->end_date ? $this->period->end_date->format('F j, Y') : $this->period->name);

        // Row 1: Organization Name
        $row1 = $blankRow;
        $row1[1] = 'Lipa Archdiocesan Social Action Commission Inc.';
        $rows[] = $row1;

        // Row 2: Address
        $row2 = $blankRow;
        $row2[1] = 'LAFORCE Bldg. J.P Luarel Highway Marawoy, Lipa City';
        $rows[] = $row2;

        // Row 3: Blank
        $rows[] = $blankRow;

        // Row 4: Cut-off line
        $row4 = $blankRow;
        $row4[1] = "Payroll for the cut-off {$cutoffText}";
        $rows[] = $row4;

        // Row 5: Payroll Period line
        $row5 = $blankRow;
        $row5[1] = "Payroll Period: {$paymentDateText}";
        $rows[] = $row5;

        // Row 6: Blank
        $rows[] = $blankRow;

        // Row 7: CONTRIBUTED SERVICES
        $row7 = $blankRow;
        $row7[1] = 'CONTRIBUTED SERVICES';
        $rows[] = $row7;

        // Row 8: Blank
        $rows[] = $blankRow;

        // Row 9-10: Acknowledgment statement
        $row9 = $blankRow;
        $row9[1] = 'I hereby acknowledge to have received from Lipa Archdiocesan Social Action Commission the sum specified opposite';
        $rows[] = $row9;

        $row10 = $blankRow;
        $row10[1] = "our respective name, as payment for my contributed services rendered for the period {$periodRangeText}";
        $rows[] = $row10;

        // Row 11: Blank
        $rows[] = $blankRow;

        // Row 12: Main Table Header Row 1
        $header1 = $blankRow;
        $header1[1]  = "EMPLOYEE'S NAME";
        $header1[2]  = 'Salary & Allowances ';
        $header1[3]  = 'Transpo Allowance';
        $header1[4]  = 'Meal Allowance';
        $header1[5]  = 'Total Compensation';
        $header1[6]  = 'DEDUCTIONS';
        $header1[14] = 'Net Pay';
        $header1[16] = 'SOURCE OF SALARY FOR FINANCE';
        $rows[] = $header1;

        // Row 13: Main Table Header Row 2
        $header2 = $blankRow;
        $header2[1]  = 'SUPPORT SERVICES';
        $header2[6]  = 'Absences';
        $header2[7]  = 'Late';
        $header2[8]  = 'Phil Health';
        $header2[9]  = 'HDMF';
        $header2[10] = 'HDMF Loan';
        $header2[11] = 'SSS Loan';
        $header2[12] = 'Witholding Tax';
        $header2[13] = 'Total Deductions';
        $header2[17] = 'BASIC ';
        $header2[18] = 'PER DAY';
        $header2[19] = 'PER HOUR';
        $header2[20] = 'PER MIN';
        $header2[21] = 'Allowance';
        $header2[22] = 'PER DAY';
        $header2[23] = 'TOTAL COMP';
        $header2[24] = 'daily with allowance';
        $header2[25] = 'Current TC';
        $header2[26] = 'Current/day with allowance';
        $header2[27] = 'New TC 4/1/25';
        $header2[28] = 'PER DAY with Allowance';
        $header2[29] = 'PER HOUR with Allowance';
        $header2[30] = 'PER MIN with Allowance';
        $rows[] = $header2;

        // Build Department Summary buckets from actual HRIS Departments
        $allDepartments = Department::orderBy('id')->get();
        $departmentNetTotals = [];
        foreach ($allDepartments as $dept) {
            $label = $dept->code ?: $dept->name;
            $departmentNetTotals[$dept->id] = [
                'label' => $label,
                'name'  => $dept->name,
                'total' => 0.0,
            ];
        }
        $unassignedNetTotal = 0.0;

        // Group payslips by Department
        $grouped = $this->payslips->groupBy(fn($ps) => $ps->employee?->department?->name ?? 'Unassigned');

        $cutoffType = $this->period->cutoff_type ?? PayrollPeriod::detectCutoffType($this->period->start_date, $this->period->end_date);
        $loanService = app(LoanService::class);

        foreach ($grouped as $deptName => $deptPayslips) {
            // Department header row
            $deptRow = $blankRow;
            $deptRow[1] = $deptName;
            $rows[] = $deptRow;
            $this->departmentHeaderRows[] = count($rows);

            $empCounter = 1;
            foreach ($deptPayslips as $ps) {
                $emp = $ps->employee;
                $comp = $emp?->compensation;

                $salaryAndAllowances = round((float) $ps->salary_and_allowances, 2);
                $totalMonthly = $comp && $comp->total_compensation > 0
                    ? (float) $comp->total_compensation
                    : round($salaryAndAllowances * 2, 2);

                $basicMonthly = $comp && $comp->basic_salary_80 > 0
                    ? (float) $comp->basic_salary_80
                    : round($totalMonthly * 0.80, 2);

                $allowanceMonthly = $comp && $comp->allowance_20 > 0
                    ? (float) $comp->allowance_20
                    : round($totalMonthly - $basicMonthly, 2);

                // Semi-monthly allowance breakdown (60% Transpo, 40% Meal)
                $semiMonthlyAllowance = round($allowanceMonthly / 2, 2);
                $transpoAllowance = round($semiMonthlyAllowance * 0.60, 2);
                $mealAllowance = round($semiMonthlyAllowance - $transpoAllowance, 2);

                // Deductions breakdown
                $absencesDed = round((float) $ps->absences_deduction, 2);
                $lateAndUndertimeDed = round((float) $ps->late_deduction + (float) $ps->undertime_deduction, 2);
                $phDed = round((float) $ps->ph_deduction, 2);
                $hdmfDed = round((float) $ps->hdmf_deduction, 2);
                $sssContrib = round((float) $ps->sss_deduction, 2);

                // Split loans into HDMF Loan and SSS Loan
                $hdmfLoan = 0.0;
                $sssLoan = 0.0;
                $otherLoan = 0.0;

                if ($emp && (float) $ps->loan_deduction > 0) {
                    $scheduled = $loanService->getScheduledDeductionsForPeriod($emp, $this->period, $cutoffType);
                    foreach ($scheduled['items'] as $loanItem) {
                        $lType = strtolower((string) ($loanItem['loan_type'] ?? ''));
                        $lName = strtolower((string) ($loanItem['loan_name'] ?? ''));
                        if (str_contains($lType, 'pagibig') || str_contains($lType, 'hdmf') || str_contains($lName, 'hdmf') || str_contains($lName, 'pag-ibig')) {
                            $hdmfLoan += (float) $loanItem['amount'];
                        } elseif (str_contains($lType, 'sss') || str_contains($lName, 'sss')) {
                            $sssLoan += (float) $loanItem['amount'];
                        } else {
                            $otherLoan += (float) $loanItem['amount'];
                        }
                    }
                    $allocatedLoans = $hdmfLoan + $sssLoan + $otherLoan;
                    if ($allocatedLoans < (float) $ps->loan_deduction) {
                        $sssLoan += ((float) $ps->loan_deduction - $allocatedLoans);
                    }
                }

                // Include SSS contribution in SSS column on end-month cutoffs so Total Deductions matches
                $sssTotalCol = round($sssLoan + $sssContrib, 2);

                $customDeductions = (float) $ps->customItems->where('type', 'deduction')->sum('amount');
                $withholdingOrOther = round((float) ($ps->other_deductions ?? 0) + $customDeductions + $otherLoan, 2);

                $totalDeductions = round((float) $ps->total_deductions, 2);
                $grossComp = round((float) $ps->gross_earnings, 2);
                $netPay = round((float) $ps->net_pay, 2);

                // Finance source of salary calculations (261 days/yr = 21.75 days/mo)
                $basicPerDay = $basicMonthly > 0 ? round(($basicMonthly * 12) / 261, 2) : 0;
                $basicPerHour = $basicPerDay > 0 ? round($basicPerDay / 8, 3) : 0;
                $basicPerMin = $basicPerHour > 0 ? round($basicPerHour / 60, 4) : 0;

                $allowancePerDay = $allowanceMonthly > 0 ? round(($allowanceMonthly * 12) / 261, 2) : 0;

                $perDayWithAllowance = $totalMonthly > 0 ? round(($totalMonthly * 12) / 261, 2) : (float) $ps->per_day_rate;
                $perHourWithAllowance = $perDayWithAllowance > 0 ? round($perDayWithAllowance / 8, 2) : (float) $ps->per_hour_rate;
                $perMinWithAllowance = $perHourWithAllowance > 0 ? round($perHourWithAllowance / 60, 4) : (float) $ps->per_minute_rate;

                $fullName = $emp?->name ?? 'Unknown';
                $shortName = trim(explode(',', $fullName)[0]);
                if ($shortName === $fullName && str_contains($fullName, ' ')) {
                    $parts = explode(' ', $fullName);
                    $shortName = $parts[0];
                }

                $empRow = $blankRow;
                $empRow[0]  = $empCounter++;
                $empRow[1]  = $fullName;
                $empRow[2]  = $salaryAndAllowances;
                $empRow[3]  = $transpoAllowance;
                $empRow[4]  = $mealAllowance;
                $empRow[5]  = $grossComp;
                $empRow[6]  = $absencesDed > 0 ? $absencesDed : '';
                $empRow[7]  = $lateAndUndertimeDed > 0 ? $lateAndUndertimeDed : '';
                $empRow[8]  = $phDed > 0 ? $phDed : '';
                $empRow[9]  = $hdmfDed > 0 ? $hdmfDed : '';
                $empRow[10] = $hdmfLoan > 0 ? round($hdmfLoan, 2) : '';
                $empRow[11] = $sssTotalCol > 0 ? $sssTotalCol : '';
                $empRow[12] = $withholdingOrOther > 0 ? $withholdingOrOther : '';
                $empRow[13] = $totalDeductions;
                $empRow[14] = $netPay;
                $empRow[15] = '';
                $empRow[16] = $shortName;
                $empRow[17] = $basicMonthly;
                $empRow[18] = $basicPerDay;
                $empRow[19] = $basicPerHour;
                $empRow[20] = $basicPerMin;
                $empRow[21] = $allowanceMonthly;
                $empRow[22] = $allowancePerDay;
                $empRow[23] = $totalMonthly;
                $empRow[24] = '';
                $empRow[25] = '';
                $empRow[26] = $basicMonthly;
                $empRow[27] = '';
                $empRow[28] = $perDayWithAllowance;
                $empRow[29] = $perHourWithAllowance;
                $empRow[30] = $perMinWithAllowance;

                $rows[] = $empRow;
                $this->employeeRows[] = count($rows);

                // Accumulate into the employee's Department net salary total
                $deptId = $emp?->department_id;
                if ($deptId && isset($departmentNetTotals[$deptId])) {
                    $departmentNetTotals[$deptId]['total'] = round($departmentNetTotals[$deptId]['total'] + $netPay, 2);
                } elseif ($emp?->department) {
                    $departmentNetTotals[$emp->department->id] = [
                        'label' => $emp->department->code ?: $emp->department->name,
                        'name'  => $emp->department->name,
                        'total' => $netPay,
                    ];
                } else {
                    $unassignedNetTotal = round($unassignedNetTotal + $netPay, 2);
                }
            }

            // Department Subtotal Row
            $subRow = $blankRow;
            $subRow[1]  = "Subtotal - {$deptName}";
            $subRow[2]  = round((float) $deptPayslips->sum('salary_and_allowances'), 2);
            $subRow[5]  = round((float) $deptPayslips->sum('gross_earnings'), 2);
            $subRow[6]  = round((float) $deptPayslips->sum('absences_deduction'), 2);
            $subRow[7]  = round((float) $deptPayslips->sum('late_deduction') + (float) $deptPayslips->sum('undertime_deduction'), 2);
            $subRow[8]  = round((float) $deptPayslips->sum('ph_deduction'), 2);
            $subRow[9]  = round((float) $deptPayslips->sum('hdmf_deduction'), 2);
            $subRow[13] = round((float) $deptPayslips->sum('total_deductions'), 2);
            $subRow[14] = round((float) $deptPayslips->sum('net_pay'), 2);
            $rows[] = $subRow;
            $this->subtotalRows[] = count($rows);
        }

        // Blank separator row before SUMMARY OF NET SALARY
        $rows[] = $blankRow;

        // SUMMARY OF NET SALARY (by Department)
        $sumHeader = $blankRow;
        $sumHeader[1] = 'SUMMARY OF NET SALARY';
        $rows[] = $sumHeader;
        $this->summaryTitleRow = count($rows);

        $grandTotalNet = 0.0;
        foreach ($departmentNetTotals as $deptSummary) {
            $sRow = $blankRow;
            $sRow[1] = $deptSummary['label'];
            $sRow[2] = round($deptSummary['total'], 2);
            $rows[] = $sRow;
            $grandTotalNet += $deptSummary['total'];
        }

        if ($unassignedNetTotal > 0) {
            $sRow = $blankRow;
            $sRow[1] = 'Unassigned';
            $sRow[2] = round($unassignedNetTotal, 2);
            $rows[] = $sRow;
            $grandTotalNet += $unassignedNetTotal;
        }

        $totalRow = $blankRow;
        $totalRow[1] = 'TOTAL';
        $totalRow[2] = round($grandTotalNet, 2);
        $rows[] = $totalRow;
        $this->summaryTotalRow = count($rows);

        $this->totalRowCount = count($rows);

        return $rows;
    }

    public function styles(Worksheet $sheet): ?array
    {
        $lastRow = max(13, $this->totalRowCount);

        // Merge cells in the 2-level table header (Rows 12 & 13)
        $sheet->mergeCells('C12:C13');
        $sheet->mergeCells('D12:D13');
        $sheet->mergeCells('E12:E13');
        $sheet->mergeCells('F12:F13');
        $sheet->mergeCells('G12:N12'); // DEDUCTIONS
        $sheet->mergeCells('O12:O13'); // Net Pay
        $sheet->mergeCells('Q12:AE12'); // SOURCE OF SALARY FOR FINANCE

        // Configure print page setup for compact single-page-wide printing
        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        $pageSetup->setHorizontalCentered(true);
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(12, 13);

        // Set tight print margins (0.15 in left/right, 0.2 in top/bottom)
        $margins = $sheet->getPageMargins();
        $margins->setTop(0.2);
        $margins->setBottom(0.2);
        $margins->setLeft(0.15);
        $margins->setRight(0.15);

        // Base compact font across entire sheet
        $sheet->getStyle("A1:AE{$lastRow}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 8,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style Title / Header Block (Rows 1 to 10)
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('B4:B5')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('B7')->getFont()->setBold(true)->setSize(9)->setUnderline(true);
        $sheet->getStyle('B9:B10')->getFont()->setItalic(true)->setSize(8);

        // Style Table Headers (Rows 12 & 13)
        $sheet->getStyle('A12:AE13')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 7.5,
                'color' => ['argb' => 'FF0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF64748B'],
                ],
            ],
        ]);

        $sheet->getRowDimension(12)->setRowHeight(18);
        $sheet->getRowDimension(13)->setRowHeight(24);

        // Apply thin grid borders to the main table body
        $lastTableRow = !empty($this->subtotalRows) ? max($this->subtotalRows) : 13;
        if ($lastTableRow > 13) {
            $sheet->getStyle("A14:O{$lastTableRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCBD5E1'],
                    ],
                ],
            ]);
            $sheet->getStyle("Q14:AE{$lastTableRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCBD5E1'],
                    ],
                ],
            ]);
            $sheet->getStyle("C14:O{$lastTableRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("R14:AE{$lastTableRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A14:A{$lastTableRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Style Department Header Rows
        foreach ($this->departmentHeaderRows as $r) {
            $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF7F1D1D']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFEE2E2'],
                ],
            ]);
            $sheet->getRowDimension($r)->setRowHeight(15);
        }

        // Style Employee Data Rows with compact height
        foreach ($this->employeeRows as $r) {
            $sheet->getRowDimension($r)->setRowHeight(14);
        }

        // Style Department Subtotal Rows
        foreach ($this->subtotalRows as $r) {
            $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8FAFC'],
                ],
            ]);
            $sheet->getRowDimension($r)->setRowHeight(15);
        }

        // Style SUMMARY OF NET SALARY block
        if ($this->summaryTitleRow > 0 && $this->summaryTotalRow >= $this->summaryTitleRow) {
            $sheet->getStyle("B{$this->summaryTitleRow}:C{$this->summaryTotalRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("B{$this->summaryTitleRow}:C{$this->summaryTitleRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ]);
            $sheet->getStyle("B{$this->summaryTotalRow}:C{$this->summaryTotalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFEF2F2'],
                ],
            ]);
            $sheet->getStyle("C{$this->summaryTitleRow}:C{$this->summaryTotalRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }
}
