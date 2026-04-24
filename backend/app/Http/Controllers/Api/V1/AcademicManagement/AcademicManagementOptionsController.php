<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicManagement\AcademicTermResource;
use App\Http\Resources\AcademicManagement\AcademicYearResource;
use App\Http\Resources\AcademicManagement\GradingStructureResource;
use App\Http\Resources\AcademicManagement\SchoolClassResource;
use App\Http\Resources\AcademicManagement\SectionResource;
use App\Http\Resources\AcademicManagement\SubjectResource;
use App\Models\HR\Staff;
use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicManagement\GradingStructure;
use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\JsonResponse;

class AcademicManagementOptionsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'academic_years' => AcademicYearResource::collection(AcademicYear::query()->get()),
                'terms' => AcademicTermResource::collection(AcademicTerm::query()->get()),
                'classes' => SchoolClassResource::collection(SchoolClass::query()->with('sections')->get()),
                'sections' => SectionResource::collection(Section::query()->with('schoolClass')->get()),
                'subjects' => SubjectResource::collection(Subject::query()->get()),
                'grading_structures' => GradingStructureResource::collection(GradingStructure::query()->with('scaleItems')->get()),
                'staff' => Staff::query()
                    ->select(['id', 'employee_code', 'full_name', 'email', 'staff_type', 'current_status'])
                    ->orderBy('full_name')
                    ->get()
                    ->map(fn (Staff $staff) => [
                        'id' => $staff->id,
                        'name' => $staff->full_name,
                        'employee_code' => $staff->employee_code,
                        'email' => $staff->email,
                        'staff_type' => $staff->staff_type,
                        'current_status' => $staff->current_status,
                    ]),
            ],
        ]);
    }
}
