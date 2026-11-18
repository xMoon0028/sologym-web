<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PsgcService
{
    protected static ?array $regions = null;
    protected static ?array $provinces = null;
    protected static ?array $cities = null;
    protected static ?array $barangaysByCity = null;

    protected string $psgcPath;

    public function __construct()
    {
        $this->psgcPath = storage_path('app/psgc');
    }

    /**
     * Get all 17 regions in standard format.
     */
    public function getRegions(): array
    {
        if (self::$regions !== null) {
            return self::$regions;
        }

        $file = $this->psgcPath . '/regions.json';
        if (!File::exists($file)) {
            return [];
        }

        $raw = json_decode(File::get($file), true) ?: [];
        $regions = [];

        foreach ($raw as $r) {
            $name = $r['region_name'];
            if ($r['region_code'] === '09') {
                $name = 'Region IX (Zamboanga Peninsula)';
            } elseif ($r['region_code'] === '15') {
                $name = 'BARMM (Bangsamoro Autonomous Region)';
            }

            $regions[] = [
                'code' => (string)$r['region_code'],
                'name' => $name,
            ];
        }

        // Ensure CALABARZON, NCR, Central Luzon are easily sorted or ordered logically
        self::$regions = $regions;
        return self::$regions;
    }

    /**
     * Get all provinces for a given region (by code or name).
     */
    public function getProvinces(string $regionQuery): array
    {
        $regionCode = $this->resolveRegionCode($regionQuery);
        if (!$regionCode) {
            return [];
        }

        if (self::$provinces === null) {
            $file = $this->psgcPath . '/provinces.json';
            self::$provinces = File::exists($file) ? json_decode(File::get($file), true) ?: [] : [];
        }

        // Special handling for NCR (13)
        if ($regionCode === '13') {
            return [
                ['code' => '1300', 'name' => 'Metro Manila'],
                ['code' => '1339', 'name' => 'City of Manila (1st District)'],
                ['code' => '1374', 'name' => 'NCR 2nd District (Mandaluyong, Marikina, Pasig, QC, San Juan)'],
                ['code' => '1375', 'name' => 'NCR 3rd District (Caloocan, Malabon, Navotas, Valenzuela)'],
                ['code' => '1376', 'name' => 'NCR 4th District (Las Piñas, Makati, Muntinlupa, Parañaque, Pasay, Taguig)'],
            ];
        }

        $result = [];
        $seen = [];

        foreach (self::$provinces as $p) {
            if ($p['region_code'] === $regionCode) {
                $pName = trim($p['province_name']);
                if (!isset($seen[strtolower($pName)])) {
                    $seen[strtolower($pName)] = true;
                    $result[] = [
                        'code' => (string)$p['province_code'],
                        'name' => $pName,
                    ];
                }
            }
        }

        // Sort alphabetically
        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $result;
    }

    /**
     * Get all cities / municipalities for a given province (by code or name).
     */
    public function getCities(string $provinceQuery): array
    {
        $provinceCode = $this->resolveProvinceCode($provinceQuery);

        if (self::$cities === null) {
            $file = $this->psgcPath . '/cities.json';
            self::$cities = File::exists($file) ? json_decode(File::get($file), true) ?: [] : [];
        }

        $result = [];
        $seen = [];

        // Special handling for Metro Manila (1300 or name)
        if ($provinceCode === '1300' || strcasecmp(trim($provinceQuery), 'Metro Manila') === 0) {
            $ncrCodes = ['1339', '1374', '1375', '1376'];
            foreach (self::$cities as $c) {
                if (in_array($c['province_code'], $ncrCodes) || ($c['region_desc'] ?? '') === '13') {
                    $cleanName = $this->cleanCityName($c['city_name']);
                    if (!isset($seen[strtolower($cleanName)])) {
                        $seen[strtolower($cleanName)] = true;
                        $result[] = [
                            'code' => (string)$c['city_code'],
                            'name' => $cleanName,
                        ];
                    }
                }
            }
            usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));
            return $result;
        }

        foreach (self::$cities as $c) {
            if ($c['province_code'] === $provinceCode || ($provinceCode === null && strcasecmp($this->cleanCityName($c['city_name']), $provinceQuery) === 0)) {
                $cleanName = $this->cleanCityName($c['city_name']);
                if (!isset($seen[strtolower($cleanName)])) {
                    $seen[strtolower($cleanName)] = true;
                    $result[] = [
                        'code' => (string)$c['city_code'],
                        'name' => $cleanName,
                    ];
                }
            }
        }

        // Sort alphabetically
        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $result;
    }

    /**
     * Get all barangays for a given city / municipality (by code or name).
     */
    public function getBarangays(string $cityQuery): array
    {
        $cityCode = $this->resolveCityCode($cityQuery);
        if (!$cityCode) {
            return [];
        }

        if (self::$barangaysByCity === null) {
            $file = $this->psgcPath . '/barangays_by_city.json';
            if (File::exists($file)) {
                self::$barangaysByCity = json_decode(File::get($file), true) ?: [];
            } else {
                // Fallback from raw barangay.json
                $rawFile = $this->psgcPath . '/barangays.json';
                $raw = File::exists($rawFile) ? json_decode(File::get($rawFile), true) ?: [] : [];
                $grouped = [];
                foreach ($raw as $b) {
                    $grouped[$b['city_code']][] = $b['brgy_name'];
                }
                self::$barangaysByCity = $grouped;
                File::put($file, json_encode($grouped));
            }
        }

        $list = self::$barangaysByCity[$cityCode] ?? [];

        // Special alias check for Lipa City (Marawoy / Marauoy)
        if ($cityCode === '041014') {
            if (in_array('Marauoy', $list) && !in_array('Marawoy', $list)) {
                $list[] = 'Marawoy';
            }
        }

        sort($list, SORT_NATURAL | SORT_FLAG_CASE);
        return array_values(array_unique($list));
    }

    /**
     * Resolve region code from code or descriptive name.
     */
    public function resolveRegionCode(string $query): ?string
    {
        $q = trim($query);
        if (is_numeric($q) && strlen($q) <= 2) {
            return str_pad($q, 2, '0', STR_PAD_LEFT);
        }

        $regions = $this->getRegions();
        foreach ($regions as $r) {
            if ($r['code'] === $q || strcasecmp($r['name'], $q) === 0) {
                return $r['code'];
            }
        }

        $qLower = strtolower($q);
        if (str_contains($qLower, 'calabarzon') || str_contains($qLower, 'iv-a') || str_contains($qLower, '4a')) return '04';
        if (str_contains($qLower, 'ncr') || str_contains($qLower, 'national capital') || str_contains($qLower, 'metro manila')) return '13';
        if (str_contains($qLower, 'mimaropa') || str_contains($qLower, 'iv-b') || str_contains($qLower, '4b')) return '17';
        if (str_contains($qLower, 'central luzon') || str_contains($qLower, 'iii') || str_contains($qLower, 'region 3')) return '03';
        if (str_contains($qLower, 'bicol') || str_contains($qLower, 'region v') || str_contains($qLower, 'region 5')) return '05';
        if (str_contains($qLower, 'ilocos') || str_contains($qLower, 'region i') || str_contains($qLower, 'region 1')) return '01';
        if (str_contains($qLower, 'cagayan valley') || str_contains($qLower, 'region ii') || str_contains($qLower, 'region 2')) return '02';
        if (str_contains($qLower, 'cordillera') || str_contains($qLower, 'car')) return '14';
        if (str_contains($qLower, 'western visayas') || str_contains($qLower, 'region vi') || str_contains($qLower, 'region 6')) return '06';
        if (str_contains($qLower, 'central visayas') || str_contains($qLower, 'region vii') || str_contains($qLower, 'region 7')) return '07';
        if (str_contains($qLower, 'eastern visayas') || str_contains($qLower, 'region viii') || str_contains($qLower, 'region 8')) return '08';
        if (str_contains($qLower, 'zamboanga') || str_contains($qLower, 'region ix') || str_contains($qLower, 'region 9')) return '09';
        if (str_contains($qLower, 'northern mindanao') || str_contains($qLower, 'region x') || str_contains($qLower, 'region 10')) return '10';
        if (str_contains($qLower, 'davao') || str_contains($qLower, 'region xi') || str_contains($qLower, 'region 11')) return '11';
        if (str_contains($qLower, 'soccsksargen') || str_contains($qLower, 'region xii') || str_contains($qLower, 'region 12')) return '12';
        if (str_contains($qLower, 'caraga') || str_contains($qLower, 'region xiii') || str_contains($qLower, 'region 13')) return '16';
        if (str_contains($qLower, 'barmm') || str_contains($qLower, 'armm') || str_contains($qLower, 'muslim mindanao')) return '15';

        return null;
    }

    /**
     * Resolve province code from code or name.
     */
    public function resolveProvinceCode(string $query): ?string
    {
        $q = trim($query);
        if (is_numeric($q) && strlen($q) >= 4) {
            return $q;
        }

        if (strcasecmp($q, 'Metro Manila') === 0) {
            return '1300';
        }

        if (self::$provinces === null) {
            $file = $this->psgcPath . '/provinces.json';
            self::$provinces = File::exists($file) ? json_decode(File::get($file), true) ?: [] : [];
        }

        foreach (self::$provinces as $p) {
            if ($p['province_code'] === $q || strcasecmp($p['province_name'], $q) === 0) {
                return (string)$p['province_code'];
            }
        }

        $qLower = strtolower($q);
        foreach (self::$provinces as $p) {
            if (str_contains(strtolower($p['province_name']), $qLower) || str_contains($qLower, strtolower($p['province_name']))) {
                return (string)$p['province_code'];
            }
        }

        return null;
    }

    /**
     * Resolve city code from code or name.
     */
    public function resolveCityCode(string $query): ?string
    {
        $q = trim($query);
        if (is_numeric($q) && strlen($q) >= 6) {
            return $q;
        }

        if (self::$cities === null) {
            $file = $this->psgcPath . '/cities.json';
            self::$cities = File::exists($file) ? json_decode(File::get($file), true) ?: [] : [];
        }

        $cleanQ = $this->cleanCityName($q);

        // Exact match
        foreach (self::$cities as $c) {
            if ($c['city_code'] === $q || strcasecmp($c['city_name'], $q) === 0 || strcasecmp($this->cleanCityName($c['city_name']), $cleanQ) === 0) {
                return (string)$c['city_code'];
            }
        }

        // Substring / partial match
        $qLower = strtolower($cleanQ);
        foreach (self::$cities as $c) {
            $cLower = strtolower($this->cleanCityName($c['city_name']));
            if ($cLower === $qLower || str_starts_with($cLower, $qLower) || str_starts_with($qLower, $cLower)) {
                return (string)$c['city_code'];
            }
        }

        return null;
    }

    /**
     * Format clean city/municipality name without uppercase quirks or extra "(Capital)".
     */
    protected function cleanCityName(string $name): string
    {
        $clean = trim($name);

        // Normalize "(Capital)" suffix if present
        $clean = preg_replace('/\s*\(Capital\)$/i', '', $clean);

        // "City Of Lipa" -> "Lipa City"
        if (preg_match('/^City Of (.*)$/i', $clean, $m)) {
            $clean = trim($m[1]) . ' City';
        }

        // Title case for all caps
        if (ctype_upper(str_replace([' ', '-', '.'], '', $clean))) {
            $clean = ucwords(strtolower($clean));
        }

        return $clean;
    }
}
