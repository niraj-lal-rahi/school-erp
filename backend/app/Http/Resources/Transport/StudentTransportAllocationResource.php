<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTransportAllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'route_id' => $this->route_id,
            'route_vehicle_assignment_id' => $this->route_vehicle_assignment_id,
            'pickup_stop_id' => $this->pickup_stop_id,
            'drop_stop_id' => $this->drop_stop_id,
            'allocated_from' => optional($this->allocated_from)->toDateString(),
            'allocated_to' => optional($this->allocated_to)->toDateString(),
            'fare_amount' => $this->fare_amount,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
                'roll_no' => $this->student->roll_no,
            ] : null),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
            'route' => $this->whenLoaded('route', fn () => $this->route ? [
                'id' => $this->route->id,
                'name' => $this->route->name,
                'code' => $this->route->code,
            ] : null),
            'route_assignment' => new TransportRouteVehicleAssignmentResource($this->whenLoaded('routeAssignment')),
            'pickup_stop' => new TransportRouteStopResource($this->whenLoaded('pickupStop')),
            'drop_stop' => new TransportRouteStopResource($this->whenLoaded('dropStop')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
