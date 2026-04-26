<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportTripLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transport_trip_id' => $this->transport_trip_id,
            'student_id' => $this->student_id,
            'staff_id' => $this->staff_id,
            'route_stop_id' => $this->route_stop_id,
            'user_type' => $this->user_type,
            'event_type' => $this->event_type,
            'event_time' => optional($this->event_time)->toAtomString(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'remarks' => $this->remarks,
            'trip' => $this->whenLoaded('trip', fn () => $this->trip ? [
                'id' => $this->trip->id,
                'trip_date' => optional($this->trip->trip_date)->toDateString(),
                'trip_type' => $this->trip->trip_type,
                'status' => $this->trip->status,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
            ] : null),
            'staff' => $this->whenLoaded('staff', fn () => $this->staff ? [
                'id' => $this->staff->id,
                'full_name' => $this->staff->full_name,
                'employee_code' => $this->staff->employee_code,
            ] : null),
            'route_stop' => new TransportRouteStopResource($this->whenLoaded('routeStop')),
            'marked_by' => $this->whenLoaded('marker', fn () => $this->marker ? [
                'id' => $this->marker->id,
                'name' => $this->marker->name,
                'email' => $this->marker->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
