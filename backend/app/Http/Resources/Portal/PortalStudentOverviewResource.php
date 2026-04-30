<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalStudentOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student' => $this->resource['student'] ?? null,
            'attendance' => $this->resource['attendance'] ?? null,
            'fees' => $this->resource['fees'] ?? null,
            'results' => $this->resource['results'] ?? null,
            'timetable' => $this->resource['timetable'] ?? null,
            'announcements' => $this->resource['announcements'] ?? null,
            'permissions' => $this->resource['permissions'] ?? null,
        ];
    }
}
