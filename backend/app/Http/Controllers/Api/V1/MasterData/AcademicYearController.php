<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => AcademicYear::query()
                ->orderByDesc('is_current')
                ->orderByDesc('start_date')
                ->get(),
        ]);
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        if ($request->boolean('is_current')) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create([
            'uuid' => (string) Str::uuid(),
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Academic year created successfully.',
            'data' => $academicYear,
        ], 201);
    }
}
