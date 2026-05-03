<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateLocalizationSettingRequest extends SettingRequest
{
    public function rules(): array
    {
        return [
            'timezone' => ['sometimes', 'timezone'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'date_format' => ['sometimes', 'string', 'max:30'],
            'time_format' => ['sometimes', 'string', 'max:30'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'currency_symbol' => ['sometimes', 'string', 'max:10'],
            'first_day_of_week' => ['sometimes', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
        ];
    }
}
