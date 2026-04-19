<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SchoolClassController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SchoolClass::query()
                ->with(['academicYear', 'sections'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $schoolClass = SchoolClass::create([
            'uuid' => (string) Str::uuid(),
            'sort_order' => 0,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Class created successfully.',
            'data' => $schoolClass->load(['academicYear', 'sections']),
        ], 201);
    }
}
