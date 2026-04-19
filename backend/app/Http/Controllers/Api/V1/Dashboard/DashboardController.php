<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'data' => [
                'students' => [
                    'total' => Student::query()->count(),
                    'active' => Student::query()->where('status', 'active')->count(),
                    'inactive' => Student::query()->where('status', 'inactive')->count(),
                    'alumni' => Student::query()->where('status', 'alumni')->count(),
                ],
                'admissions' => [
                    'applied' => Admission::query()->where('status', 'applied')->count(),
                    'reviewing' => Admission::query()->where('status', 'reviewing')->count(),
                    'admitted' => Admission::query()->where('status', 'admitted')->count(),
                ],
                'guardians_total' => Guardian::query()->count(),
                'sections_total' => Section::query()->count(),
                'recent_students' => Student::query()
                    ->latest()
                    ->take(5)
                    ->get(['id', 'admission_no', 'first_name', 'last_name', 'status', 'created_at']),
            ],
        ]);
    }
}
