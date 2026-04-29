<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'module' => $this->module,
            'description' => $this->description,
            'query_config' => $this->query_config,
            'default_filters' => $this->default_filters,
            'is_system' => (bool) $this->is_system,
            'status' => $this->status,
            'schedules_count' => $this->whenCounted('schedules'),
            'runs_count' => $this->whenCounted('runs'),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'schedules' => $this->whenLoaded('schedules', fn () => $this->schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'schedule_type' => $schedule->schedule_type,
                'schedule_config' => $schedule->schedule_config,
                'next_run_at' => optional($schedule->next_run_at)->toAtomString(),
                'last_run_at' => optional($schedule->last_run_at)->toAtomString(),
                'channel' => $schedule->channel,
                'recipients' => $schedule->recipients,
                'status' => $schedule->status,
            ])->values()->all()),
            'runs' => ReportRunResource::collection($this->whenLoaded('runs')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
