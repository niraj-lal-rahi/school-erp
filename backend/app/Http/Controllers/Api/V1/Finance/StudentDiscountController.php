<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\StudentDiscountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertStudentDiscountRequest;
use App\Http\Resources\Finance\StudentDiscountResource;
use App\Models\Finance\StudentDiscount;
use App\Services\Finance\StudentDiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDiscountController extends Controller
{
    public function __construct(
        protected StudentDiscountService $studentDiscounts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentDiscount::class);

        return response()->json(
            StudentDiscountResource::collection(
                $this->studentDiscounts->paginate(
                    $request->only(['search', 'student_id', 'academic_year_id', 'status']),
                    (int) $request->integer('per_page', 15),
                )
            )->response()->getData(true)
        );
    }

    public function store(UpsertStudentDiscountRequest $request): JsonResponse
    {
        $studentDiscount = $this->studentDiscounts->create(StudentDiscountData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'status' => $request->validated('status') ?? 'pending',
        ]));

        return response()->json([
            'message' => 'Student discount created successfully.',
            'data' => new StudentDiscountResource($studentDiscount),
        ], 201);
    }

    public function show(StudentDiscount $studentDiscount): JsonResponse
    {
        $this->authorize('view', $studentDiscount);

        return response()->json([
            'data' => new StudentDiscountResource($studentDiscount->load(['student', 'academicYear', 'discountType', 'feeHead', 'approver'])),
        ]);
    }

    public function update(UpsertStudentDiscountRequest $request, StudentDiscount $studentDiscount): JsonResponse
    {
        $studentDiscount = $this->studentDiscounts->update($studentDiscount, StudentDiscountData::fromArray([
            ...$request->validated(),
            'school_id' => $studentDiscount->school_id,
            'approved_by' => $studentDiscount->approved_by,
            'approved_at' => $studentDiscount->approved_at,
            'status' => $request->validated('status') ?? $studentDiscount->status,
        ]));

        return response()->json([
            'message' => 'Student discount updated successfully.',
            'data' => new StudentDiscountResource($studentDiscount),
        ]);
    }

    public function destroy(StudentDiscount $studentDiscount): JsonResponse
    {
        $this->authorize('delete', $studentDiscount);
        $this->studentDiscounts->delete($studentDiscount);

        return response()->json(null, 204);
    }

    public function approve(StudentDiscount $studentDiscount, Request $request): JsonResponse
    {
        $this->authorize('update', $studentDiscount);
        $studentDiscount = $this->studentDiscounts->approve($studentDiscount, $request->user()->id);

        return response()->json([
            'message' => 'Student discount approved successfully.',
            'data' => new StudentDiscountResource($studentDiscount),
        ]);
    }

    public function reject(StudentDiscount $studentDiscount): JsonResponse
    {
        $this->authorize('update', $studentDiscount);
        $studentDiscount = $this->studentDiscounts->reject($studentDiscount);

        return response()->json([
            'message' => 'Student discount rejected successfully.',
            'data' => new StudentDiscountResource($studentDiscount),
        ]);
    }
}
