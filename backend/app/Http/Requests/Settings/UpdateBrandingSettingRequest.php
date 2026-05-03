<?php

namespace App\Http\Requests\Settings;

class UpdateBrandingSettingRequest extends SettingRequest
{
    public function rules(): array
    {
        $hex = ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'];

        return [
            'school_name' => ['sometimes', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'max:4096'],
            'favicon' => ['nullable', 'file', 'image', 'max:2048'],
            'primary_color' => $hex,
            'secondary_color' => $hex,
            'accent_color' => $hex,
            'footer_text' => ['nullable', 'string', 'max:255'],
            'custom_css' => ['nullable', 'string'],
        ];
    }
}
