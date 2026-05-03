<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'automation_rule_id' => $this->automation_rule_id,
            'status' => $this->status,
            'started_at' => optional($this->started_at)?->toAtomString(),
            'completed_at' => optional($this->completed_at)?->toAtomString(),
            'records_processed' => $this->records_processed,
            'success_count' => $this->success_count,
            'failed_count' => $this->failed_count,
            'error_message' => $this->error_message,
            'metadata' => $this->metadata,
            'automation_rule' => $this->whenLoaded('automationRule', fn () => $this->automationRule ? new AutomationRuleResource($this->automationRule) : null),
            'action_logs' => $this->whenLoaded('actionLogs', fn () => $this->actionLogs->map(fn ($log) => [
                'id' => $log->id,
                'action_type' => $log->action_type,
                'reference_type' => $log->reference_type,
                'reference_id' => $log->reference_id,
                'status' => $log->status,
                'payload' => $log->payload,
                'response' => $log->response,
                'error_message' => $log->error_message,
                'executed_at' => optional($log->executed_at)?->toAtomString(),
            ])),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
