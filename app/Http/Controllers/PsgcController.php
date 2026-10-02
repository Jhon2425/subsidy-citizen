<?php

namespace App\Http\Controllers;

use App\Services\PsgcService;
use Illuminate\Http\JsonResponse;

class PsgcController extends Controller
{
    public function __construct(protected PsgcService $psgc)
    {
    }

    public function regions(): JsonResponse
    {
        return response()->json($this->psgc->regions());
    }

    public function provinces(string $regionCode): JsonResponse
    {
        $provinces = $this->psgc->provinces($regionCode);

        // NCR has districts rather than provinces. Tell the client to
        // load cities straight from the region instead.
        return response()->json([
            'provinces' => $provinces,
            'skip_province' => empty($provinces),
        ]);
    }

    public function cities(string $provinceCode): JsonResponse
    {
        return response()->json($this->psgc->cities($provinceCode));
    }

    public function citiesInRegion(string $regionCode): JsonResponse
    {
        return response()->json($this->psgc->citiesInRegion($regionCode));
    }

    public function barangays(string $cityCode): JsonResponse
    {
        return response()->json($this->psgc->barangays($cityCode));
    }
}