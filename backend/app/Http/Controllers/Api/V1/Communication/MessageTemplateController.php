<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreMessageTemplateRequest;
use App\Http\Requests\Communication\UpdateMessageTemplateRequest;
use App\Models\Communication\MessageTemplate;
use App\Services\Communication\MessageTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function __construct(
        protected MessageTemplateService $templates,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->templates->paginate(
                $request->only(['search', 'status', 'channel']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreMessageTemplateRequest $request): JsonResponse
    {
        $template = $this->templates->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Message template created successfully.',
            'data' => $template,
        ], 201);
    }

    public function show(MessageTemplate $template): JsonResponse
    {
        return response()->json([
            'data' => $this->templates->findOrFail($template->id),
        ]);
    }

    public function update(UpdateMessageTemplateRequest $request, MessageTemplate $template): JsonResponse
    {
        $template = $this->templates->update($template, $request->validated());

        return response()->json([
            'message' => 'Message template updated successfully.',
            'data' => $template,
        ]);
    }

    public function destroy(MessageTemplate $template): JsonResponse
    {
        $this->templates->delete($template);

        return response()->json(null, 204);
    }
}
