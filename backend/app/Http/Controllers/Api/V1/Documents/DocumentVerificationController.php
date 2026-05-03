<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\RejectDocumentRequest;
use App\Http\Requests\Documents\VerifyDocumentRequest;
use App\Http\Resources\Documents\DocumentVerificationResource;
use App\Models\Documents\DocumentVerification;
use App\Services\Documents\DocumentPermissionService;
use App\Services\Documents\DocumentService;
use App\Services\Documents\DocumentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentVerificationController extends BaseDocumentController
{
    public function __construct(
        protected DocumentService $documents,
        protected DocumentVerificationService $verifications,
        protected DocumentPermissionService $permissions,
    ) {
    }

    public function verify(VerifyDocumentRequest $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('verify', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'verify', $this->permissions);
        $verification = $this->verifications->verify($document, $request->user(), $request->validated('remarks'));

        return response()->json([
            'message' => 'Document verified successfully.',
            'data' => new DocumentVerificationResource($verification),
        ]);
    }

    public function reject(RejectDocumentRequest $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('verify', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'verify', $this->permissions);
        $verification = $this->verifications->reject($document, $request->user(), $request->validated('remarks'));

        return response()->json([
            'message' => 'Document rejected successfully.',
            'data' => new DocumentVerificationResource($verification),
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DocumentVerification::class);

        return response()->json([
            'data' => DocumentVerificationResource::collection($this->verifications->pending(
                $request->only(['date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }
}
