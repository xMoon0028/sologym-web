<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTraining;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::where('is_active', true)->count();

        // 1. Address Analytics: Municipality / City Distribution
        $addresses = EmployeeAddress::whereHas('employee', fn($q) => $q->where('is_active', true))->get();
        $cityCounts = [];
        $provinceCounts = [];

        foreach ($addresses as $addr) {
            $city = trim($addr->municipality_city ?: '');
            $province = trim($addr->province ?: '');

            // Fallback parsing from full address text if structured field is empty
            if (empty($city)) {
                $fullAddr = ($addr->permanent_address ?? '') . ' ' . ($addr->current_address ?? '');
                $city = $this->detectCityFromText($fullAddr) ?: 'Unspecified Municipality';
            }

            if (empty($province)) {
                $fullAddr = ($addr->permanent_address ?? '') . ' ' . ($addr->current_address ?? '');
                $province = $this->detectProvinceFromText($fullAddr) ?: 'Batangas';
            }

            $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
            $provinceCounts[$province] = ($provinceCounts[$province] ?? 0) + 1;
        }

        arsort($cityCounts);
        arsort($provinceCounts);

        $totalWithAddress = count($addresses);
        $municipalityData = [];
        foreach ($cityCounts as $cName => $cnt) {
            $municipalityData[] = [
                'city' => $cName,
                'count' => $cnt,
                'percentage' => $totalWithAddress > 0 ? round(($cnt / $totalWithAddress) * 100, 1) : 0,
            ];
        }

        // 2. Department / Unit Distribution (The 6 Units)
        $departments = Department::withCount(['employees' => fn($q) => $q->where('is_active', true)])->get();
        $deptData = $departments->map(function ($d) use ($totalEmployees) {
            return [
                'name' => $d->name,
                'code' => $d->code,
                'count' => $d->employees_count,
                'percentage' => $totalEmployees > 0 ? round(($d->employees_count / $totalEmployees) * 100, 1) : 0,
            ];
        });

        // 3. Gender / Sex Distribution
        $genderCounts = Employee::where('is_active', true)
            ->select('sex', DB::raw('count(*) as count'))
            ->groupBy('sex')
            ->pluck('count', 'sex')
            ->toArray();

        $males = $genderCounts['Male'] ?? 0;
        $females = $genderCounts['Female'] ?? 0;
        $otherGender = $totalEmployees - ($males + $females);

        // 4. Employment Status Distribution
        $statusCounts = Employee::where('is_active', true)
            ->select('employment_status', DB::raw('count(*) as count'))
            ->groupBy('employment_status')
            ->pluck('count', 'employment_status')
            ->toArray();

        // 5. Age Bracket Distribution
        $activeEmployees = Employee::where('is_active', true)->get();
        $ageBrackets = [
            'Under 25' => 0,
            '25 - 34' => 0,
            '35 - 44' => 0,
            '45 - 54' => 0,
            '55 and above' => 0,
        ];
        foreach ($activeEmployees as $emp) {
            $age = $emp->age;
            if ($age === null) continue;
            if ($age < 25) $ageBrackets['Under 25']++;
            elseif ($age <= 34) $ageBrackets['25 - 34']++;
            elseif ($age <= 44) $ageBrackets['35 - 44']++;
            elseif ($age <= 54) $ageBrackets['45 - 54']++;
            else $ageBrackets['55 and above']++;
        }

        // 6. Tenure / Years of Service Distribution
        $tenureBrackets = [
            'Less than 1 yr' => 0,
            '1 - 3 yrs' => 0,
            '3 - 5 yrs' => 0,
            '5+ yrs' => 0,
        ];
        $totalYearsOfService = 0;
        $tenureCount = 0;

        foreach ($activeEmployees as $emp) {
            $y = $emp->years_of_service;
            if ($y !== null) {
                $totalYearsOfService += $y;
                $tenureCount++;
                if ($y < 1.0) $tenureBrackets['Less than 1 yr']++;
                elseif ($y <= 3.0) $tenureBrackets['1 - 3 yrs']++;
                elseif ($y <= 5.0) $tenureBrackets['3 - 5 yrs']++;
                else $tenureBrackets['5+ yrs']++;
            }
        }
        $avgTenure = $tenureCount > 0 ? round($totalYearsOfService / $tenureCount, 1) : 0;

        // 7. Civil Status Distribution
        $civilCounts = Employee::where('is_active', true)
            ->select('civil_status', DB::raw('count(*) as count'))
            ->groupBy('civil_status')
            ->pluck('count', 'civil_status')
            ->toArray();

        // 8. Certificates Compliance
        $totalCerts = CertificateTraining::count();
        $expiringSoon = CertificateTraining::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();
        $expiredCerts = CertificateTraining::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();
        $validCerts = $totalCerts - ($expiringSoon + $expiredCerts);

        return view('admin.analytics.index', compact(
            'totalEmployees',
            'totalWithAddress',
            'municipalityData',
            'provinceCounts',
            'deptData',
            'males',
            'females',
            'otherGender',
            'statusCounts',
            'ageBrackets',
            'tenureBrackets',
            'avgTenure',
            'civilCounts',
            'totalCerts',
            'expiringSoon',
            'expiredCerts',
            'validCerts'
        ));
    }

    private function detectCityFromText(string $text): ?string
    {
        $knownCities = [
            'Lipa City' => ['Lipa City', 'Lipa'],
            'Batangas City' => ['Batangas City'],
            'Tanauan City' => ['Tanauan City', 'Tanauan'],
            'Santo Tomas City' => ['Santo Tomas', 'Sto. Tomas', 'Sto Tomas'],
            'San Jose' => ['San Jose'],
            'Rosario' => ['Rosario'],
            'Bauan' => ['Bauan'],
            'San Juan' => ['San Juan'],
            'Ibaan' => ['Ibaan'],
            'Lemery' => ['Lemery'],
            'Calaca City' => ['Calaca City', 'Calaca'],
            'Balayan' => ['Balayan'],
            'Taal' => ['Taal'],
            'Nasugbu' => ['Nasugbu'],
            'Lian' => ['Lian'],
            'Mabini' => ['Mabini'],
            'Cuenca' => ['Cuenca'],
            'Alitagtag' => ['Alitagtag'],
            'Malvar' => ['Malvar'],
            'Padre Garcia' => ['Padre Garcia'],
            'San Pascual' => ['San Pascual'],
            'Taysan' => ['Taysan'],
            'Lobo' => ['Lobo'],
            'Laurel' => ['Laurel'],
            'Talisay' => ['Talisay'],
            'Agoncillo' => ['Agoncillo'],
            'San Nicolas' => ['San Nicolas'],
            'Santa Teresita' => ['Santa Teresita', 'Sta. Teresita'],
            'Mataasnakahoy' => ['Mataasnakahoy', 'Mataas na Kahoy'],
            'Tuy' => ['Tuy'],
            'Calatagan' => ['Calatagan'],
            'Lucena City' => ['Lucena City', 'Lucena'],
            'San Pablo City' => ['San Pablo City', 'San Pablo'],
            'Calamba City' => ['Calamba City', 'Calamba'],
            'Quezon City' => ['Quezon City'],
            'Manila' => ['Manila'],
        ];

        foreach ($knownCities as $canonicalName => $keywords) {
            foreach ($keywords as $kw) {
                if (stripos($text, $kw) !== false) {
                    return $canonicalName;
                }
            }
        }

        return null;
    }

    private function detectProvinceFromText(string $text): ?string
    {
        $provinces = ['Batangas', 'Laguna', 'Cavite', 'Rizal', 'Quezon', 'Metro Manila'];
        foreach ($provinces as $p) {
            if (stripos($text, $p) !== false) {
                return $p;
            }
        }
        return null;
    }
}
