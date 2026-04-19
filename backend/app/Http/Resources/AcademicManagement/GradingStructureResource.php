<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradingStructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'description' => $this->description,
            'pass_percentage' => $this->pass_percentage,
            'status' => $this->status,
            'scale_items' => GradeScaleItemResource::collection($this->whenLoaded('scaleItems')),
        ];
    }
}
