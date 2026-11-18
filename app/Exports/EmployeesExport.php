<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function collection(): \Illuminate\Support\Enumerable
    {
        return Employee::with([
            'department',
            'address',
            'governmentIds',
            'compensation',
            'education',
            'iq',
            'lastEmployment',
            'family',
            'certificates',
            'timeCreditLedger',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'ID No.',
            'Full Name',
            'Position',
            'Dept.',
            'SG',
            'Status',
            'Date Hired',
            'Yrs Svc',
            'Birthdate',
            'Age',
            'Sex',
            'Civil Status',
            'Region',
            'Province',
            'City/Mun.',
            'Barangay',
            'Permanent Address',
            'Current Address',
            'ZIP',
            'SSS No.',
            'Pag-IBIG No.',
            'PhilHealth No.',
            'TIN',
            'Basic (80%)',
            'Allow. (20%)',
            'Total Comp.',
            'SSS Ded.',
            'PH Ded.',
            'PI Ded.',
            'Primary School',
            'Secondary School',
            'Stanine',
            'Verbal',
            'Non-Verbal',
            'Holland Fit',
            'Prev. Company',
            'Prev. Position',
            'Last Salary',
            'Reason Leaving',
            'Marriage',
            'Single Status',
            'Spouse Work',
            'Children',
            'Siblings',
            'Dependents',
            'Certs',
            'Offset (Hrs)',
            'Offset (Days)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 9,  'B' => 18, 'C' => 13, 'D' => 10, 'E' => 6,  'F' => 10,
            'G' => 10, 'H' => 7,  'I' => 10, 'J' => 5,  'K' => 6,  'L' => 9,
            'M' => 9,  'N' => 10, 'O' => 11, 'P' => 11, 'Q' => 16, 'R' => 16,
            'S' => 7,  'T' => 12, 'U' => 12, 'V' => 12, 'W' => 12, 'X' => 10,
            'Y' => 10, 'Z' => 11, 'AA' => 8, 'AB' => 8, 'AC' => 8, 'AD' => 14,
            'AE' => 14, 'AF' => 7, 'AG' => 7, 'AH' => 8, 'AI' => 10, 'AJ' => 13,
            'AK' => 12, 'AL' => 10, 'AM' => 13, 'AN' => 9, 'AO' => 9, 'AP' => 11,
            'AQ' => 7,  'AR' => 7,  'AS' => 12, 'AT' => 6, 'AU' => 9, 'AV' => 9,
        ];
    }

    public function map($employee): array
    {
        $comp = $employee->compensation;
        $addr = $employee->address;
        $gov = $employee->governmentIds;
        $edu = $employee->education;
        $iq = $employee->iq;
        $prev = $employee->lastEmployment;
        $fam = $employee->family;

        $timeCreditBalance = $employee->time_credit_balance;

        return [
            $employee->employee_no ?? '',
            $employee->name,
            $employee->position ?? '',
            $employee->department->code ?? ($employee->department->name ?? ''),
            $employee->salary_grade ?? '',
            $employee->employment_status ?? '',
            $employee->date_hired ? $employee->date_hired->format('Y-m-d') : '',
            $employee->years_of_service ?? '',
            $employee->birthdate ? $employee->birthdate->format('Y-m-d') : '',
            $employee->age ?? '',
            $employee->sex ?? '',
            $employee->civil_status ?? '',
            $addr->region ?? '',
            $addr->province ?? '',
            $addr->municipality_city ?? '',
            $addr->barangay ?? '',
            $addr->permanent_address ?? '',
            $addr->current_address ?? '',
            $addr->zip_code ?? '',
            $gov->sss_no ?? '',
            $gov->pagibig_no ?? '',
            $gov->philhealth_no ?? '',
            $gov->tin_no ?? '',
            $comp ? $comp->basic_salary_80 : 0,
            $comp ? $comp->allowance_20 : 0,
            $comp ? $comp->total_compensation : 0,
            $comp ? $comp->sss_deduction : 0,
            $comp ? $comp->ph_deduction : 0,
            $comp ? $comp->pi_deduction : 0,
            $edu->primary_school ?? '',
            $edu->secondary_school ?? '',
            $iq->total_stanine ?? '',
            $iq->verbal ?? '',
            $iq->non_verbal ?? '',
            $iq->holland_personality ?? '',
            $prev->company ?? '',
            $prev->position ?? '',
            $prev ? $prev->last_salary : '',
            $prev->reason_for_leaving ?? '',
            $fam->marriage_type ?? '',
            $fam->single_status ?? '',
            $fam->spouse_occupation ?? '',
            $fam->no_of_children ?? 0,
            $fam->no_of_siblings ?? 0,
            $fam->dependents ?? '',
            $employee->certificates->count(),
            number_format($timeCreditBalance, 2),
            number_format(round($timeCreditBalance / 8, 1), 1),
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $highestRow = max(1, $sheet->getHighestRow());
        $fullRange = "A1:AV{$highestRow}";

        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(1, 1);

        $margins = $sheet->getPageMargins();
        $margins->setTop(0.25);
        $margins->setBottom(0.25);
        $margins->setLeft(0.2);
        $margins->setRight(0.2);

        $sheet->getStyle($fullRange)->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 8,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF991B1B'], // Red-800
                ],
            ],
        ];
    }
}
