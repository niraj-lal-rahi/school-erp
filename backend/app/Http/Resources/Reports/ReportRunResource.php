<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_definition_id' => $this->report_definition_id,
            'run_type' => $this->run_type,
            'status' => $this->status,
            'started_at' => optional($this->started_at)->toAtomString(),
            'completed_at' => optional($this->completed_at)->toAtomString(),
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'parameters' => $this->parameters,
            'error_message' => $this->error_message,
            'initiated_by' => $this->initiated_by,
            'report_definition' => $this->whenLoaded('reportDefinition', fn () => $this->reportDefinition ? [
                'id' => $this->reportDefinition->id,
                'name' => $this->reportDefinition->name,
                'code' => $this->reportDefinition->code,
                'module' => $this->reportDefinition->module,
                'status' => $this->reportDefinition->status,
            ] : null),
            'initiator' => $this->whenLoaded('initiator', fn () => $this->initiator ? [
                'id' => $this->initiator->id,
                'name' => $this->initiator->name,
                'email' => $this->initiator->email,
            ] : null),
            'exports' => ReportExportResource::collection($this->whenLoaded('exports')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
