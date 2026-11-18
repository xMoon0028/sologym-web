<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeGovernmentId;
use App\Models\EmployeeIq;
use App\Models\EmployeeLastEmployment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmployeeImport implements ToCollection, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;

    /**
     * Process raw collection of rows (handles both 2-row merged headers and single-row headers).
     */
    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        // 1. Detect header rows and build unified column map
        $headerInfo = $this->buildHeaderMap($rows);
        $headerMap = $headerInfo['map'];
        $dataStartIdx = $headerInfo['dataStartIdx'];

        // 2. Iterate data rows
        $totalRows = count($rows);
        for ($i = $dataStartIdx; $i < $totalRows; $i++) {
            $row = $rows[$i];
            $rowArr = is_array($row) ? $row : $row->toArray();

            // Skip empty rows
            $nonEmpty = array_filter($rowArr, fn($v) => !is_null($v) && trim((string)$v) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            try {
                $this->processEmployeeRow($rowArr, $headerMap, $i + 1);
            } catch (\Throwable $e) {
                $this->errors[] = "Row " . ($i + 1) . " error: " . $e->getMessage();
            }
        }
    }

    /**
     * Build unified header map by detecting 1-row or 2-row merged headers.
     */
    protected function buildHeaderMap(Collection $rows): array
    {
        $maxScan = min(5, count($rows));
        $nameRowIdx = null;

        // Find which row contains "NAME"
        for ($i = 0; $i < $maxScan; $i++) {
            $rowArr = is_array($rows[$i]) ? $rows[$i] : $rows[$i]->toArray();
            foreach ($rowArr as $cell) {
                $str = strtoupper(trim((string)$cell));
                if ($str === 'NAME' || $str === 'EMPLOYEE NAME' || $str === 'FULL NAME') {
                    $nameRowIdx = $i;
                    break 2;
                }
            }
        }

        // Default to row 0 if not found
        if ($nameRowIdx === null) {
            $nameRowIdx = 0;
        }

        $row1 = is_array($rows[$nameRowIdx]) ? $rows[$nameRowIdx] : $rows[$nameRowIdx]->toArray();
        $row2 = isset($rows[$nameRowIdx + 1])
            ? (is_array($rows[$nameRowIdx + 1]) ? $rows[$nameRowIdx + 1] : $rows[$nameRowIdx + 1]->toArray())
            : [];

        // Check if row2 is a sub-header row (e.g. contains "Basic Salary", "Allowance", "Primary", "Civil Wedding")
        $isTwoRowHeader = false;
        $subHeaderIndicators = ['BASIC SALARY', 'ALLOWANCE', 'TOTAL COMPENSATION', 'PRIMARY', 'STANINE', 'CIVIL WEDDING', 'NO. OF SIBLINGS'];
        foreach ($row2 as $cell) {
            $u = strtoupper(trim((string)$cell));
            foreach ($subHeaderIndicators as $ind) {
                if (str_contains($u, $ind)) {
                    $isTwoRowHeader = true;
                    break 2;
                }
            }
        }

        $headerMap = []; // colIndex => normalizedKey
        $maxCols = max(count($row1), count($row2));
        $lastGroup = '';

        for ($c = 0; $c < $maxCols; $c++) {
            $h1 = trim((string)($row1[$c] ?? ''));
            $h2 = $isTwoRowHeader ? trim((string)($row2[$c] ?? '')) : '';

            if ($h1 !== '') {
                $lastGroup = $h1;
            }

            $combined = '';
            if ($h2 !== '') {
                // If sub-header exists, combine with group or use sub-header
                $combined = ($lastGroup ? $lastGroup . ' ' : '') . $h2;
            } else {
                $combined = $h1;
            }

            if ($combined !== '') {
                $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($combined)));
                $headerMap[$c] = trim($normalized, '_');
            }
        }

        return [
            'map' => $headerMap,
            'dataStartIdx' => $isTwoRowHeader ? ($nameRowIdx + 2) : ($nameRowIdx + 1),
        ];
    }

    /**
     * Process an individual employee data row.
     */
    protected function processEmployeeRow(array $row, array $headerMap, int $rowNumber): void
    {
        // Build associative dictionary: key => value
        $data = [];
        foreach ($headerMap as $colIdx => $key) {
            $data[$key] = $row[$colIdx] ?? null;
        }

        // 1. Extract Name
        $name = $this->getAny($data, [
            'name',
            'employee_name',
            'full_name',
            'emp_name',
            'complete_name',
        ]);

        if (empty($name)) {
            // Fallback by positional column B (index 1)
            $name = !empty($row[1]) && !is_numeric($row[1]) ? trim((string)$row[1]) : null;
        }

        if (empty($name)) {
            return; // Skip non-employee rows
        }

        // 2. Department & Position
        $deptCell = $this->getAny($data, [
            'office_section_unit_program',
            'office_section_unit',
            'department',
            'unit',
            'office',
            'section',
            'program',
        ]) ?? ($row[2] ?? null);

        $resolvedDeptName = $this->resolveDepartmentName((string)$deptCell);
        $dept = Department::firstOrCreate(
            ['name' => $resolvedDeptName],
            ['code' => strtoupper(substr(str_replace(' ', '', $resolvedDeptName), 0, 5))]
        );

        $position = $this->getAny($data, [
            'position',
            'job_title',
            'designation',
            'title',
        ]);
        if (empty($position) && !empty($deptCell) && $deptCell !== $resolvedDeptName) {
            // If office cell contains specific role like "Community-Based DRRM Program Specialist I", use as position
            $position = trim((string)$deptCell);
        }

        // 3. Employee ID
        $empNo = $this->getAny($data, [
            'employee_no',
            'id_number',
            'emp_no',
            'id_no',
            'id',
        ]) ?? ($row[0] ?? null);

        // Auto-assign clean employee no if missing
        if (empty($empNo) || is_numeric($empNo) && (int)$empNo < 100) {
            $existing = Employee::where('name', $name)->first();
            $empNo = $existing?->employee_no;
            if (!$empNo) {
                $lastId = Employee::max('id') + 1;
                $empNo = sprintf('%03d-%s', $lastId, date('y'));
            }
        }

        // 4. Dates & Demographics
        $dateHired = $this->parseDate($this->getAny($data, ['date_hired', 'date_hire', 'hired_date']) ?? ($row[5] ?? null));
        $birthdate = $this->parseDate($this->getAny($data, ['birthdate', 'birth_date', 'dob']) ?? ($row[6] ?? null));
        $salaryGrade = $this->getAny($data, ['salary_grade', 'sg', 'grade']) ?? ($row[3] ?? null);
        $employmentStatus = $this->getAny($data, ['employment_status', 'status', 'emp_status']) ?? ($row[4] ?? 'Regular');
        $civilStatus = $this->getAny($data, ['civil_status', 'civil']) ?? ($row[7] ?? null);
        $sex = $this->getAny($data, ['gender', 'sex']) ?? ($row[8] ?? null);

        // Normalize Sex
        if ($sex) {
            $sexLower = strtolower(trim((string)$sex));
            $sex = str_starts_with($sexLower, 'f') ? 'Female' : 'Male';
        }

        // 5. Create or Update Base Employee
        $linkedUser = User::where('name', trim((string)$name))
            ->orWhere('name', 'like', '%' . explode(',', trim((string)$name))[0] . '%')
            ->first();

        $employee = Employee::updateOrCreate(
            ['employee_no' => $empNo],
            [
                'user_id'           => $linkedUser?->id,
                'name'              => trim((string)$name),
                'department_id'     => $dept->id,
                'position'          => $position ? trim((string)$position) : 'Staff',
                'salary_grade'      => $salaryGrade ? trim((string)$salaryGrade) : null,
                'employment_status' => $employmentStatus ? trim((string)$employmentStatus) : 'Regular',
                'date_hired'        => $dateHired,
                'birthdate'         => $birthdate,
                'sex'               => $sex,
                'civil_status'      => $civilStatus ? trim((string)$civilStatus) : null,
                'is_active'         => true,
            ]
        );

        // 6. Address
        $permAddress = $this->getAny($data, ['permanent_address', 'address_permanent', 'address']) ?? ($row[10] ?? null);
        $currAddress = $this->getAny($data, ['current_address', 'address_current']) ?? ($row[11] ?? $permAddress);
        $zipCode = $this->getAny($data, ['zip_code', 'zip', 'postal_code']) ?? ($row[12] ?? null);

        $region = $this->getAny($data, ['region']) ?? 'Region IV-A (CALABARZON)';
        $province = $this->getAny($data, ['province']) ?? 'Batangas';
        $municipalityCity = $this->getAny($data, ['municipality_city', 'municipality', 'city']);
        $barangay = $this->getAny($data, ['barangay', 'brgy']);

        // Auto-detect Batangas city/municipality from address text if not in dedicated column
        if (empty($municipalityCity) && !empty($permAddress)) {
            $knownCities = [
                'Lipa', 'Batangas City', 'Tanauan', 'Santo Tomas', 'Calaca', 'Agoncillo', 'Alitagtag',
                'Balayan', 'Balete', 'Bauan', 'Calatagan', 'Cuenca', 'Ibaan', 'Laurel', 'Lemery', 'Lian',
                'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Nasugbu', 'Padre Garcia', 'Rosario',
                'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita',
                'Taal', 'Talisay', 'Taysan', 'Tingloy', 'Tuy', 'Lucena', 'San Pablo', 'Calamba'
            ];
            foreach ($knownCities as $city) {
                if (stripos((string)$permAddress, $city) !== false) {
                    $municipalityCity = str_ends_with(strtolower($city), 'city') ? $city : ($city . (in_array($city, ['Lipa', 'Tanauan', 'Santo Tomas', 'Calaca']) ? ' City' : ''));
                    break;
                }
            }
        }

        EmployeeAddress::updateOrCreate(['employee_id' => $employee->id], [
            'region'            => $region ? trim((string)$region) : 'Region IV-A (CALABARZON)',
            'province'          => $province ? trim((string)$province) : 'Batangas',
            'municipality_city' => $municipalityCity ? trim((string)$municipalityCity) : null,
            'barangay'          => $barangay ? trim((string)$barangay) : null,
            'permanent_address' => $permAddress ? trim((string)$permAddress) : null,
            'current_address'   => $currAddress ? trim((string)$currAddress) : null,
            'zip_code'          => $zipCode ? trim((string)$zipCode) : null,
        ]);

        // 7. Government IDs
        $tin = $this->getAny($data, ['tin', 'tin_no']) ?? ($row[13] ?? null);
        $pagibig = $this->getAny($data, ['pag_ibig', 'pagibig', 'hdmf', 'pag_ibig_no']) ?? ($row[14] ?? null);
        $philhealth = $this->getAny($data, ['philhealth', 'phil_health', 'ph_no']) ?? ($row[15] ?? null);
        $sss = $this->getAny($data, ['sss', 'sss_no', 'sss_number']) ?? ($row[16] ?? null);

        EmployeeGovernmentId::updateOrCreate(['employee_id' => $employee->id], [
            'tin_no'        => $tin ? trim((string)$tin) : null,
            'pagibig_no'    => $pagibig ? trim((string)$pagibig) : null,
            'philhealth_no' => $philhealth ? trim((string)$philhealth) : null,
            'sss_no'        => $sss ? trim((string)$sss) : null,
        ]);

        // 8. Compensation
        $basic = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_basic_salary_80_',
            'basic_salary_80',
            'basic_salary_80_',
            'basic_salary',
            'basic',
        ]) ?? ($row[17] ?? 0));

        $allowance = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_allowance_20_',
            'allowances_20',
            'allowance_20_',
            'allowance_20',
            'allowance',
        ]) ?? ($row[18] ?? 0));

        $totalComp = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_total_compensation',
            'total_compensation',
            'total_salary',
        ]) ?? ($row[19] ?? 0)) ?: ($basic + $allowance);

        $sssDeduction = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_sss',
            'sss_deduction',
        ]) ?? ($row[20] ?? 0));

        $phDeduction = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_ph',
            'ph_deduction',
        ]) ?? ($row[21] ?? 0));

        $piDeduction = $this->parseAmount($this->getAny($data, [
            'compensation_and_regular_deductions_pi',
            'pi_deduction',
            'hdmf_deduction',
        ]) ?? ($row[22] ?? 0));

        EmployeeCompensation::updateOrCreate(['employee_id' => $employee->id], [
            'basic_salary_80'    => $basic,
            'allowance_20'       => $allowance,
            'total_compensation' => $totalComp,
            'sss_deduction'      => $sssDeduction,
            'ph_deduction'       => $phDeduction,
            'pi_deduction'       => $piDeduction,
        ]);

        // 9. Educational Background
        $primary = $this->getAny($data, [
            'educational_background_primary',
            'primary',
            'primary_school',
            'elementary',
        ]) ?? ($row[23] ?? null);

        $secondary = $this->getAny($data, [
            'educational_background_secondary',
            'secondary',
            'secondary_school',
            'high_school',
        ]) ?? ($row[24] ?? null);

        EmployeeEducation::updateOrCreate(['employee_id' => $employee->id], [
            'primary_school'   => $primary ? trim((string)$primary) : null,
            'secondary_school' => $secondary ? trim((string)$secondary) : null,
        ]);

        // 10. IQ Test Result
        $stanine = $this->getAny($data, [
            'iq_test_result_total_stanine',
            'total_stanine',
            'stanine',
        ]) ?? ($row[25] ?? null);

        $verbal = $this->getAny($data, [
            'iq_test_result_verbal',
            'verbal',
        ]) ?? ($row[26] ?? null);

        $nonVerbal = $this->getAny($data, [
            'iq_test_result_non_verbal',
            'non_verbal',
        ]) ?? ($row[27] ?? null);

        $holland = $this->getAny($data, [
            'iq_test_result_holland_personality_job_fit',
            'holland_personality_job_fit',
            'holland_personality',
        ]) ?? ($row[28] ?? null);

        EmployeeIq::updateOrCreate(['employee_id' => $employee->id], [
            'total_stanine'       => $stanine ? trim((string)$stanine) : null,
            'verbal'              => $verbal ? trim((string)$verbal) : null,
            'non_verbal'          => $nonVerbal ? trim((string)$nonVerbal) : null,
            'holland_personality' => $holland ? trim((string)$holland) : null,
        ]);

        // 11. Last Employment
        $company = $this->getAny($data, [
            'last_employment_company',
            'company',
            'previous_company',
        ]) ?? ($row[29] ?? null);

        $prevPosition = $this->getAny($data, [
            'last_employment_position',
            'previous_position',
        ]) ?? ($row[30] ?? null);

        $lastSalary = $this->parseAmount($this->getAny($data, [
            'last_employment_last_salary',
            'last_salary',
        ]) ?? ($row[31] ?? 0));

        $reasonLeaving = $this->getAny($data, [
            'last_employment_reason_for_leaving',
            'reason_for_leaving',
        ]) ?? ($row[32] ?? null);

        EmployeeLastEmployment::updateOrCreate(['employee_id' => $employee->id], [
            'company'            => $company ? trim((string)$company) : null,
            'position'           => $prevPosition ? trim((string)$prevPosition) : null,
            'last_salary'        => $lastSalary > 0 ? $lastSalary : null,
            'reason_for_leaving' => $reasonLeaving ? trim((string)$reasonLeaving) : null,
        ]);

        // 12. Family & Dependents
        $noSiblings = (int) ($this->getAny($data, [
            'number_of_siblings_children_no_of_siblings',
            'no_of_siblings',
            'siblings',
        ]) ?? ($row[33] ?? 0));

        $noChildren = (int) ($this->getAny($data, [
            'number_of_siblings_children_number_of_children',
            'number_of_children',
            'no_of_children',
            'children',
        ]) ?? ($row[34] ?? 0));

        $marriageCivil = $this->getAny($data, ['marriage_civil_wedding', 'civil_wedding']);
        $marriageChurch = $this->getAny($data, ['marriage_church_wedding', 'church_wedding']);
        $marriageLiveIn = $this->getAny($data, ['marriage_live_in', 'live_in']);

        $marriageType = 'single';
        if (!empty($marriageChurch) && $marriageChurch !== '-') {
            $marriageType = 'church';
        } elseif (!empty($marriageCivil) && $marriageCivil !== '-') {
            $marriageType = 'civil';
        } elseif (!empty($marriageLiveIn) && $marriageLiveIn !== '-') {
            $marriageType = 'live_in';
        }

        $spouseOccupation = $this->getAny($data, [
            'marriage_occupation_of_spouse',
            'occupation_of_spouse',
            'spouse_occupation',
        ]) ?? ($row[38] ?? null);

        $student = $this->getAny($data, ['single_student', 'student']);
        $nonStudent = $this->getAny($data, ['single_non_student', 'non_student']);
        $singleStatus = !empty($student) && $student !== '-' ? 'student' : (!empty($nonStudent) && $nonStudent !== '-' ? 'non_student' : null);

        $dependents = $this->getAny($data, [
            'single_dependents',
            'dependents',
        ]) ?? ($row[41] ?? null);

        EmployeeFamily::updateOrCreate(['employee_id' => $employee->id], [
            'marriage_type'     => $marriageType,
            'spouse_occupation' => $spouseOccupation ? trim((string)$spouseOccupation) : null,
            'no_of_siblings'    => $noSiblings,
            'no_of_children'    => $noChildren,
            'dependents'        => $dependents ? trim((string)$dependents) : null,
            'single_status'     => $singleStatus,
        ]);

        $this->imported++;
    }

    /**
     * Map spreadsheet office / unit text strictly to LASAC's official 6 units.
     */
    protected function resolveDepartmentName(string $name): string
    {
        $n = strtolower(trim($name));
        if (empty($n)) return 'Executive Office';

        // Exact match check against the 6 units first
        $officialUnits = [
            'Executive Office',
            'Administrative Services Section',
            'Finance Management Office',
            'Alay Kapwa Programs',
            'Mission Sustainability Section',
            'Resource Development Office',
        ];
        foreach ($officialUnits as $unit) {
            if (strcasecmp($unit, trim($name)) === 0) {
                return $unit;
            }
        }

        // 1. Finance Management Office
        if (str_contains($n, 'finance') || str_contains($n, 'account') || str_contains($n, 'bookkeep') || str_contains($n, 'cashier') || str_contains($n, 'budget') || str_contains($n, 'disburse') || str_contains($n, 'fmo')) {
            return 'Finance Management Office';
        }

        // 2. Resource Development Office
        if (str_contains($n, 'resource') || str_contains($n, 'mobilization') || str_contains($n, 'fundrais') || str_contains($n, 'donor') || str_contains($n, 'partner') || str_contains($n, 'rdo') || str_contains($n, 'communicat')) {
            return 'Resource Development Office';
        }

        // 3. Mission Sustainability Section
        if (str_contains($n, 'sustainability') || str_contains($n, 'mission') || str_contains($n, 'mss') || str_contains($n, 'monitoring') || str_contains($n, 'evaluation') || str_contains($n, 'advocacy') || str_contains($n, 'research')) {
            return 'Mission Sustainability Section';
        }

        // 4. Alay Kapwa Programs
        if (str_contains($n, 'alay kapwa') || str_contains($n, 'akp') || str_contains($n, 'drrm') || str_contains($n, 'community') || str_contains($n, 'social action') || str_contains($n, 'social enterprise') || str_contains($n, 'program') || str_contains($n, 'specialist') || str_contains($n, 'livelihood') || str_contains($n, 'relief') || str_contains($n, 'emergency')) {
            return 'Alay Kapwa Programs';
        }

        // 5. Administrative Services Section
        if (str_contains($n, 'admin') || str_contains($n, 'general service') || str_contains($n, 'hr') || str_contains($n, 'human resource') || str_contains($n, 'information technology') || str_contains($n, 'it') || str_contains($n, 'procurement') || str_contains($n, 'driver') || str_contains($n, 'utility') || str_contains($n, 'maintenance')) {
            return 'Administrative Services Section';
        }

        // 6. Executive Office
        if (str_contains($n, 'executive') || str_contains($n, 'secretariat') || str_contains($n, 'director') || str_contains($n, 'eo') || str_contains($n, 'corporate')) {
            return 'Executive Office';
        }

        return 'Executive Office';
    }

    /**
     * Retrieve the first non-empty value from an array using multiple key candidates.
     */
    protected function getAny(array $data, array $candidates): mixed
    {
        foreach ($candidates as $cand) {
            if (isset($data[$cand]) && $data[$cand] !== '' && !is_null($data[$cand])) {
                return $data[$cand];
            }
        }
        return null;
    }

    /**
     * Parse date string or Excel serial number into Y-m-d.
     */
    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        // Excel serial date number
        if (is_numeric($value) && (float)$value > 20000 && (float)$value < 60000) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse monetary or numeric amount.
     */
    protected function parseAmount(mixed $value): float
    {
        if ($value === null || $value === '' || $value === '-') {
            return 0.0;
        }
        return (float) preg_replace('/[^\d.]/', '', str_replace(',', '', (string) $value));
    }
}
