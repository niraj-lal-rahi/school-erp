<?php

namespace App\Http\Resources\Examination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradingSystemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'grading_type' => $this->grading_type,
            'pass_percentage' => $this->pass_percentage,
            'description' => $this->description,
            'status' => $this->status,
            'grade_scales_count' => $this->whenCounted('gradeScales'),
            'grade_scales' => $this->whenLoaded('gradeScales', fn () => $this->gradeScales->map(fn ($scale) => [
                'id' => $scale->id,
                'grade_label' => $scale->grade_label,
                'min_percentage' => $scale->min_percentage,
                'max_percentage' => $scale->max_percentage,
                'grade_point' => $scale->grade_point,
                'remarks' => $scale->remarks,
            ])->values()),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
