<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FeeInvoiceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ApplyInvoiceDiscountRequest;
use App\Http\Requests\Finance\ApplyInvoiceFineRequest;
use App\Http\Requests\Finance\UpsertFeeInvoiceRequest;
use App\Http\Resources\Finance\InvoiceListResource;
use App\Http\Resources\Finance\FeeInvoiceResource;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FineRule;
use App\Models\Finance\StudentDiscount;
use App\Services\Finance\FeeInvoiceService;
use App\Support\Api\ApiPaginationHelper;
use App\Support\Multitenancy\TenantOwnershipValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeInvoiceController extends Controller
{
    public function __construct(
        protected FeeInvoiceService $invoices,
        protected TenantOwnershipValidator $tenantOwnership,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeeInvoice::class);

        return response()->json(
            ApiPaginationHelper::fromResourceCollection(InvoiceListResource::collection($this->invoices->paginate(
                $request->only(['search', 'student_id', 'academic_year_id', 'status', 'due_date_from', 'due_date_to']),
                (int) $request->integer('per_page', 15),
            )))
        );
    }

    public function store(UpsertFeeInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->create(FeeInvoiceData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Invoice created successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ], 201);
    }

    public function show(FeeInvoice $feeInvoice): JsonResponse
    {
        $this->authorize('view', $feeInvoice);

        return response()->json([
            'data' => new FeeInvoiceResource($feeInvoice->load(['student', 'academicYear', 'creator', 'items.feeHead', 'items.installment'])),
        ]);
    }

    public function update(UpsertFeeInvoiceRequest $request, FeeInvoice $feeInvoice): JsonResponse
    {
        $invoice = $this->invoices->update($feeInvoice, FeeInvoiceData::fromArray([
            ...$request->validated(),
            'school_id' => $feeInvoice->school_id,
            'created_by' => $feeInvoice->created_by,
        ]));

        return response()->json([
            'message' => 'Invoice updated successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ]);
    }

    public function destroy(FeeInvoice $feeInvoice): JsonResponse
    {
        $this->authorize('delete', $feeInvoice);
        $this->invoices->delete($feeInvoice);

        return response()->json(null, 204);
    }

    public function issue(FeeInvoice $feeInvoice): JsonResponse
    {
        $this->authorize('update', $feeInvoice);
        $invoice = $this->invoices->issue($feeInvoice);

        return response()->json([
            'message' => 'Invoice issued successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ]);
    }

    public function cancel(FeeInvoice $feeInvoice): JsonResponse
    {
        $this->authorize('update', $feeInvoice);
        $invoice = $this->invoices->cancel($feeInvoice);

        return response()->json([
            'message' => 'Invoice cancelled successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ]);
    }

    public function applyDiscount(FeeInvoice $feeInvoice, ApplyInvoiceDiscountRequest $request): JsonResponse
    {
        $discount = StudentDiscount::query()->findOrFail((int) $request->validated('student_discount_id'));
        $this->tenantOwnership->assertUserOwnsModel(
            $request->user(),
            $discount,
            'school_id',
            'You cannot apply a discount from another tenant.'
        );
        $invoice = $this->invoices->applyDiscount($feeInvoice, $discount);

        return response()->json([
            'message' => 'Discount applied successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ]);
    }

    public function applyFine(FeeInvoice $feeInvoice, ApplyInvoiceFineRequest $request): JsonResponse
    {
        $fineRule = FineRule::query()->findOrFail((int) $request->validated('fine_rule_id'));
        $this->tenantOwnership->assertUserOwnsModel(
            $request->user(),
            $fineRule,
            'school_id',
            'You cannot apply a fine rule from another tenant.'
        );
        $invoice = $this->invoices->applyFine($feeInvoice, $fineRule);

        return response()->json([
            'message' => 'Fine applied successfully.',
            'data' => new FeeInvoiceResource($invoice),
        ]);
    }
}
