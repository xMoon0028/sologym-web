<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\CertificateTraining;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeGovernmentId;
use App\Models\EmployeeIq;
use App\Models\EmployeeLastEmployment;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\TimeCreditLedger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Employee User Account (Stephen Xavier Mendoza)
        $user = User::firstOrCreate(
            ['email' => 'stephen@lasac.com'],
            [
                'name' => 'Stephen Xavier Mendoza',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('employee');

        // Department: Alay Kapwa Programs
        $deptAkp = Department::where('code', 'AKP')->first();

        // Employee 1: Mendoza, Stephen Xavier (Lipa City)
        $emp1 = Employee::updateOrCreate(
            ['employee_no' => '251-25'],
            [
                'user_id' => $user->id,
                'name' => 'Mendoza, Stephen Xavier',
                'position' => 'Program Specialist I',
                'salary_grade' => 'Grade 4',
                'employment_status' => 'Regular',
                'department_id' => $deptAkp?->id,
                'date_hired' => '2024-03-01',
                'birthdate' => '1998-05-15',
                'sex' => 'Male',
                'civil_status' => 'Single',
                'is_active' => true,
            ]
        );

        EmployeeAddress::updateOrCreate(['employee_id' => $emp1->id], [
            'region' => 'Region IV-A (CALABARZON)',
            'province' => 'Batangas',
            'municipality_city' => 'Lipa City',
            'barangay' => 'Marawoy',
            'permanent_address' => 'Brgy. Marawoy, Lipa City, Batangas',
            'current_address' => 'Brgy. Marawoy, Lipa City, Batangas',
            'zip_code' => '4217',
        ]);

        EmployeeGovernmentId::updateOrCreate(['employee_id' => $emp1->id], [
            'sss_no' => '34-5678901-2',
            'pagibig_no' => '1210-4567-8901',
            'philhealth_no' => '19-025468791-3',
            'tin_no' => '456-789-012-000',
        ]);

        EmployeeCompensation::updateOrCreate(['employee_id' => $emp1->id], [
            'basic_salary_80' => 13517.39,
            'allowance_20' => 3379.35,
            'total_compensation' => 16896.74,
            'sss_deduction' => 675.00,
            'ph_deduction' => 0.00,
            'pi_deduction' => 0.00,
        ]);

        EmployeeEducation::updateOrCreate(['employee_id' => $emp1->id], [
            'primary_school' => 'Lipa City South Central School',
            'secondary_school' => 'Batangas State University',
        ]);

        EmployeeIq::updateOrCreate(['employee_id' => $emp1->id], [
            'total_stanine' => '7',
            'verbal' => 'High Average',
            'non_verbal' => 'Superior',
            'holland_personality' => 'Social - Enterprising - Realistic',
        ]);

        EmployeeLastEmployment::updateOrCreate(['employee_id' => $emp1->id], [
            'company' => 'Provincial Disaster Risk Reduction Office',
            'position' => 'Field Assistant',
            'last_salary' => 14000.00,
            'reason_for_leaving' => 'Career advancement and professional growth',
        ]);

        EmployeeFamily::updateOrCreate(['employee_id' => $emp1->id], [
            'marriage_type' => null,
            'single_status' => 'non_student',
            'no_of_siblings' => 2,
            'no_of_children' => 0,
            'dependents' => 'Mother (Senior Citizen)',
        ]);

        CertificateTraining::updateOrCreate(['employee_id' => $emp1->id, 'title' => 'Standard First Aid & BLS-CPR Training'], [
            'type' => 'training',
            'issuer' => 'Philippine Red Cross',
            'date_issued' => '2025-04-10',
            'expiry_date' => Carbon::now()->addDays(20),
            'notes' => 'Renewable every 2 years',
        ]);

        CertificateTraining::updateOrCreate(['employee_id' => $emp1->id, 'title' => 'Incident Command System (ICS) Level 1'], [
            'type' => 'certificate',
            'issuer' => 'Office of Civil Defense (OCD)',
            'date_issued' => '2025-08-15',
            'expiry_date' => Carbon::now()->addYears(2),
            'notes' => 'Completed 40 hours module',
        ]);

        // Attendance & time credit for Stephen
        TimeCreditLedger::updateOrCreate(['employee_id' => $emp1->id, 'date' => '2026-08-08'], [
            'type' => 'accrual',
            'hours' => 9.0,
            'reason' => 'Weekend duty (Sat, Aug 08)',
            'running_balance' => 9.0,
        ]);

        TimeCreditLedger::updateOrCreate(['employee_id' => $emp1->id, 'date' => '2026-08-14'], [
            'type' => 'accrual',
            'hours' => 2.5,
            'reason' => 'Weekday overtime after 5PM (Fri, Aug 14)',
            'running_balance' => 11.5,
        ]);

        // Additional employees across other units & municipalities
        $otherEmployees = [
            [
                'no' => '251-01',
                'name' => 'De Castro, Maria Elena',
                'dept' => 'EO',
                'pos' => 'Executive Assistant',
                'salary' => 24000.00,
                'status' => 'Regular',
                'sex' => 'Female',
                'civil' => 'Married',
                'hired' => '2021-06-15',
                'bday' => '1989-11-20',
                'city' => 'Batangas City',
                'brgy' => 'Kumintang Ibaba',
                'zip' => '4200',
            ],
            [
                'no' => '251-02',
                'name' => 'Castillo, Ramon Rafael',
                'dept' => 'ADMIN',
                'pos' => 'Administrative Officer',
                'salary' => 22000.00,
                'status' => 'Regular',
                'sex' => 'Male',
                'civil' => 'Married',
                'hired' => '2022-01-10',
                'bday' => '1992-04-18',
                'city' => 'Tanauan City',
                'brgy' => 'Poblacion 2',
                'zip' => '4232',
            ],
            [
                'no' => '251-03',
                'name' => 'Hernandez, Camille Joy',
                'dept' => 'FMO',
                'pos' => 'Finance Officer',
                'salary' => 26000.00,
                'status' => 'Regular',
                'sex' => 'Female',
                'civil' => 'Single',
                'hired' => '2020-09-01',
                'bday' => '1995-08-12',
                'city' => 'Santo Tomas City',
                'brgy' => 'San Antonio',
                'zip' => '4234',
            ],
            [
                'no' => '251-04',
                'name' => 'Dimaculangan, Carlo',
                'dept' => 'MSS',
                'pos' => 'Sustainability Officer',
                'salary' => 18500.00,
                'status' => 'Probationary',
                'sex' => 'Male',
                'civil' => 'Single',
                'hired' => '2025-02-01',
                'bday' => '2000-02-28',
                'city' => 'San Jose',
                'brgy' => 'Tugtug',
                'zip' => '4227',
            ],
            [
                'no' => '251-05',
                'name' => 'Alcantara, Patricia Mae',
                'dept' => 'RDO',
                'pos' => 'Resource Development Officer',
                'salary' => 21000.00,
                'status' => 'Regular',
                'sex' => 'Female',
                'civil' => 'Single',
                'hired' => '2023-08-15',
                'bday' => '1996-10-05',
                'city' => 'Bauan',
                'brgy' => 'Manghinao Proper',
                'zip' => '4201',
            ],
            [
                'no' => '251-06',
                'name' => 'Reyes, Joshua Angelo',
                'dept' => 'AKP',
                'pos' => 'Community Organizer',
                'salary' => 16500.00,
                'status' => 'Contractual',
                'sex' => 'Male',
                'civil' => 'Single',
                'hired' => '2025-05-15',
                'bday' => '2001-07-22',
                'city' => 'Rosario',
                'brgy' => 'Namunga',
                'zip' => '4225',
            ],
        ];

        foreach ($otherEmployees as $o) {
            $d = Department::where('code', $o['dept'])->first();
            $emp = Employee::updateOrCreate(
                ['employee_no' => $o['no']],
                [
                    'name' => $o['name'],
                    'position' => $o['pos'],
                    'salary_grade' => 'Grade 3',
                    'employment_status' => $o['status'],
                    'department_id' => $d?->id,
                    'date_hired' => $o['hired'],
                    'birthdate' => $o['bday'],
                    'sex' => $o['sex'],
                    'civil_status' => $o['civil'],
                    'is_active' => true,
                ]
            );

            EmployeeAddress::updateOrCreate(['employee_id' => $emp->id], [
                'region' => 'Region IV-A (CALABARZON)',
                'province' => 'Batangas',
                'municipality_city' => $o['city'],
                'barangay' => $o['brgy'],
                'permanent_address' => "Brgy. {$o['brgy']}, {$o['city']}, Batangas",
                'current_address' => "Brgy. {$o['brgy']}, {$o['city']}, Batangas",
                'zip_code' => $o['zip'],
            ]);

            $basic = round($o['salary'] * 0.8, 2);
            $allow = round($o['salary'] * 0.2, 2);
            EmployeeCompensation::updateOrCreate(['employee_id' => $emp->id], [
                'basic_salary_80' => $basic,
                'allowance_20' => $allow,
                'total_compensation' => $o['salary'],
                'sss_deduction' => 800.00,
                'ph_deduction' => 450.00,
                'pi_deduction' => 100.00,
            ]);

            EmployeeGovernmentId::updateOrCreate(['employee_id' => $emp->id], [
                'sss_no' => '34-' . rand(1000000, 9999999) . '-1',
                'pagibig_no' => '1210-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'philhealth_no' => '19-' . rand(100000000, 999999999) . '-2',
                'tin_no' => rand(100, 999) . '-' . rand(100, 999) . '-' . rand(100, 999) . '-000',
            ]);
        }

        // Payroll Period & Exact Matching Payslip for Stephen
        $period = PayrollPeriod::updateOrCreate(
            ['name' => 'August 6-20, 2026'],
            [
                'start_date' => '2026-08-06',
                'end_date' => '2026-08-20',
                'date_of_payment' => '2026-08-28',
                'status' => 'released',
                'finalized_at' => '2026-08-27 15:00:00',
                'released_at' => '2026-08-27 16:00:00',
            ]
        );

        Payslip::updateOrCreate(
            ['employee_id' => $emp1->id, 'period_id' => $period->id],
            [
                'per_day_rate' => 776.86,
                'per_hour_rate' => 97.11,
                'per_minute_rate' => 1.62,
                'salary_and_allowances' => 8448.37,
                'holiday_pay' => 0.00,
                'sss_deduction' => 675.00,
                'ph_deduction' => 0.00,
                'hdmf_deduction' => 0.00,
                'absent_days' => 0,
                'absences_deduction' => 0.00,
                'late_minutes' => 80,
                'late_deduction' => 129.48,
                'undertime_minutes' => 0,
                'undertime_deduction' => 0.00,
                'gross_earnings' => 8448.37,
                'total_deductions' => 804.48,
                'net_pay' => 7643.89,
                'is_finalized' => true,
            ]
        );
    }
}
