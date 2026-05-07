<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Resources\PlatformAuditLogResource;
use App\Modules\SuperAdmin\Services\PlatformAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformAuditLogController extends Controller
{
    public function __construct(
        protected PlatformAuditService $auditLogs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $logs = $this->auditLogs->paginate(
            filters: [
                'tenant_id' => $request->integer('tenant_id') ?: null,
                'user_id' => $request->integer('user_id') ?: null,
                'action' => $request->string('action')->toString() ?: null,
                'module' => $request->string('module')->toString() ?: null,
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->string('search')->toString() ?: null,
            ],
            perPage: (int) $request->integer('per_page', 20),
        );

        return response()->json([
            'data' => PlatformAuditLogResource::collection($logs->getCollection()),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'filters' => $request->only([
                    'tenant_id',
                    'user_id',
                    'action',
                    'module',
                    'date_from',
                    'date_to',
                    'search',
                ]),
            ],
        ]);
    }
}
