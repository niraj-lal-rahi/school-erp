<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeScaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_label' => $this->grade_label,
            'min_percentage' => $this->min_percentage,
            'max_percentage' => $this->max_percentage,
            'grade_point' => $this->grade_point,
            'remarks' => $this->remarks,
        ];
    }
}
