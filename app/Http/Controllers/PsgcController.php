<?php

namespace App\Http\Controllers;

use App\Services\PsgcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PsgcController extends Controller
{
    public function __construct(protected PsgcService $psgcService) {}

    public function regions(): JsonResponse
    {
        return response()->json($this->psgcService->getRegions());
    }

    public function provinces(Request $request): JsonResponse
    {
        $query = (string) ($request->query('region') ?? $request->query('region_code') ?? '');
        if (empty($query)) {
            return response()->json([]);
        }

        return response()->json($this->psgcService->getProvinces($query));
    }

    public function cities(Request $request): JsonResponse
    {
        $query = (string) ($request->query('province') ?? $request->query('province_code') ?? '');
        if (empty($query)) {
            return response()->json([]);
        }

        return response()->json($this->psgcService->getCities($query));
    }

    public function barangays(Request $request): JsonResponse
    {
        $query = (string) ($request->query('city') ?? $request->query('city_code') ?? '');
        if (empty($query)) {
            return response()->json([]);
        }

        return response()->json($this->psgcService->getBarangays($query));
    }
}
