<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocalizationSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'first_day_of_week' => $this->first_day_of_week,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
