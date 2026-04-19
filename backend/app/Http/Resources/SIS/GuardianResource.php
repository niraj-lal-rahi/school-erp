<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'relationship_type' => $this->relationship_type,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'occupation' => $this->occupation,
            'annual_income' => $this->annual_income,
            'education' => $this->education,
            'photo_path' => $this->photo_path,
            'is_primary' => (bool) $this->is_primary,
            'can_receive_sms' => (bool) $this->can_receive_sms,
            'can_receive_email' => (bool) $this->can_receive_email,
            'can_pickup_student' => (bool) $this->can_pickup_student,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'notes' => $this->notes,
            'status' => $this->status,
            'pivot' => $this->whenPivotLoaded('student_guardian', function (): array {
                return [
                    'relationship' => $this->pivot->relationship,
                    'relationship_label' => $this->pivot->relationship_label,
                    'is_primary' => (bool) $this->pivot->is_primary,
                    'is_emergency_contact' => (bool) $this->pivot->is_emergency_contact,
                    'pickup_authorized' => (bool) $this->pivot->pickup_authorized,
                    'financial_responsibility_percentage' => $this->pivot->financial_responsibility_percentage,
                    'notes' => $this->pivot->notes,
                ];
            }),
        ];
    }
}
