<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSectionRequest;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Section::query()
                ->with(['schoolClass.academicYear', 'classTeacher'])
                ->when($request->string('search')->toString(), function ($query, string $search): void {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('school_class_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        $section = Section::create([
            'uuid' => (string) Str::uuid(),
            'capacity' => 40,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Section created successfully.',
            'data' => $section->load(['schoolClass.academicYear', 'classTeacher']),
        ], 201);
    }

    public function update(StoreSectionRequest $request, Section $section): JsonResponse
    {
        $section->update($request->validated());

        return response()->json([
            'message' => 'Section updated successfully.',
            'data' => $section->fresh()->load(['schoolClass.academicYear', 'classTeacher']),
        ]);
    }

    public function destroy(Section $section): JsonResponse
    {
        $section->delete();

        return response()->json(null, 204);
    }
}
