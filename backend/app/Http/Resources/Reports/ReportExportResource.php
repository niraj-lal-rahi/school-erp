<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_run_id' => $this->report_run_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'downloaded_count' => (int) $this->downloaded_count,
            'last_downloaded_at' => optional($this->last_downloaded_at)->toAtomString(),
            'report_run' => $this->whenLoaded('reportRun', fn () => $this->reportRun ? [
                'id' => $this->reportRun->id,
                'status' => $this->reportRun->status,
                'file_type' => $this->reportRun->file_type,
                'started_at' => optional($this->reportRun->started_at)->toAtomString(),
                'completed_at' => optional($this->reportRun->completed_at)->toAtomString(),
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
