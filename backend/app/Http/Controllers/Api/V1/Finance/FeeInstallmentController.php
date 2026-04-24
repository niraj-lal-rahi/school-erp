<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FeeInstallmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\GenerateFeeInstallmentsRequest;
use App\Http\Requests\Finance\UpsertFeeInstallmentRequest;
use App\Http\Resources\Finance\FeeInstallmentResource;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\StudentFeeAssignment;
use App\Services\Finance\FeeInstallmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeInstallmentController extends Controller
{
    public function __construct(
        protected FeeInstallmentService $installments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeeInstallment::class);

        return response()->json(
            FeeInstallmentResource::collection($this->installments->paginate(
                $request->only(['search', 'student_id', 'academic_year_id', 'school_class_id', 'section_id', 'status', 'due_date_from', 'due_date_to']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertFeeInstallmentRequest $request): JsonResponse
    {
        $installment = $this->installments->create(FeeInstallmentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Fee installment created successfully.',
            'data' => new FeeInstallmentResource($installment),
        ], 201);
    }

    public function show(FeeInstallment $feeInstallment): JsonResponse
    {
        $this->authorize('view', $feeInstallment);

        return response()->json([
            'data' => new FeeInstallmentResource($feeInstallment->load([
                'feeHead',
                'studentFeeAssignment.student',
                'studentFeeAssignment.academicYear',
                'studentFeeAssignment.schoolClass',
                'studentFeeAssignment.section',
            ])),
        ]);
    }

    public function update(UpsertFeeInstallmentRequest $request, FeeInstallment $feeInstallment): JsonResponse
    {
        $installment = $this->installments->update($feeInstallment, FeeInstallmentData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Fee installment updated successfully.',
            'data' => new FeeInstallmentResource($installment),
        ]);
    }

    public function destroy(FeeInstallment $feeInstallment): JsonResponse
    {
        $this->authorize('delete', $feeInstallment);
        $this->installments->delete($feeInstallment);

        return response()->json(null, 204);
    }

    public function generate(StudentFeeAssignment $studentFeeAssignment, GenerateFeeInstallmentsRequest $request): JsonResponse
    {
        $generated = $this->installments->generateForAssignment($studentFeeAssignment);

        return response()->json([
            'message' => 'Fee installments generated successfully.',
            'data' => FeeInstallmentResource::collection($generated),
        ], 201);
    }
}
